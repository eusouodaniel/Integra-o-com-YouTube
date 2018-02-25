<?php
	require_once 'config.php';
	require_once 'DB.class.php';

	$db = new DB;

	if(isset($_POST['videoSubmit'])){
		$title = $_POST['title'];
		$desc = $_POST['description'];
		$tags = $_POST['tags'];

		if($_FILES["file"]["name"] != ''){
		    $fileName = str_shuffle('codexworld').'-'.basename($_FILES["file"]["name"]);
			$filePath = "videos/".$fileName;
			$allowedTypeArr = array("video/mp4", "video/avi", "video/mpeg", "video/mpg", "video/mov", "video/wmv", "video/rm");
			if(in_array($_FILES['file']['type'], $allowedTypeArr)){
			    if(move_uploaded_file($_FILES['file']['tmp_name'], $filePath)){
					$insert = $db->insert($title, $desc, $tags, $fileName);
					$_SESSION['uploadedFileId'] = $insert;
			    }else{
			        header("Location:".BASE_URL."index.php?err=ue");
					exit;
			    }
			}else{
				header("Location:".BASE_URL."index.php?err=fe");
				exit;
			}
		}else{
			header('Location:'.BASE_URL.'index.php?err=bf');
			exit;
		}
	}

	$videoData = $db->getRow($_SESSION['uploadedFileId']);

	$tokenSessionKey = 'token-' . $client->prepareScopes();
	if (isset($_GET['code'])) {
	  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
	    die('The session state did not match.');
	  }
	  $client->authenticate($_GET['code']);
	  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
	  header('Location: ' . REDIRECT_URL);
	}

	if (isset($_SESSION[$tokenSessionKey])) {
	  $client->setAccessToken($_SESSION[$tokenSessionKey]);
	}

	if ($client->getAccessToken()) {
	  $htmlBody = '';
	  try{
	    $videoPath = 'videos/'.$videoData['file_name'];
		if(!empty($videoData['youtube_video_id'])){
			$videoTitle = $videoData['title'];
			$videoDesc = $videoData['description'];
			$videoTags = $videoData['tags'];
			$videoId = $videoData['youtube_video_id'];
		}else{
			$snippet = new Google_Service_YouTube_VideoSnippet();
			$snippet->setTitle($videoData['title']);
			$snippet->setDescription($videoData['description']);
			$snippet->setTags(explode(",",$videoData['tags']));
			$snippet->setCategoryId("22");
			$status = new Google_Service_YouTube_VideoStatus();
			$status->privacyStatus = "public";
			$video = new Google_Service_YouTube_Video();
			$video->setSnippet($snippet);
			$video->setStatus($status);
			$chunkSizeBytes = 1 * 1024 * 1024;
			$client->setDefer(true);
			$insertRequest = $youtube->videos->insert("status,snippet", $video);
			$media = new Google_Http_MediaFileUpload(
				$client,
				$insertRequest,
				'video/*',
				null,
				true,
				$chunkSizeBytes
			);
			$media->setFileSize(filesize($videoPath));
			$status = false;
			$handle = fopen($videoPath, "rb");
			while (!$status && !feof($handle)) {
			  $chunk = fread($handle, $chunkSizeBytes);
			  $status = $media->nextChunk($chunk);
			}
			fclose($handle);
			$client->setDefer(false);
			$db->update($videoData['id'],$status['id']);
			@unlink("videos/".$videoData['file_name']);
			$videoTitle = $status['snippet']['title'];
			$videoDesc = $status['snippet']['description'];
			$videoTags = implode(",",$status['snippet']['tags']);
			$videoId = $status['id'];
		}
	    $htmlBody .= "<p class='succ-msg'>Upload para o YouTube</p>";
		$htmlBody .= '<embed width="400" height="315" src="https://www.youtube.com/embed/'.$videoId.'"></embed>';
		$htmlBody .= '<ul><li><b>Título: </b>'.$videoTitle.'</li>';
		$htmlBody .= '<li><b>Descrição: </b>'.$videoDesc.'</li>';
		$htmlBody .= '<li><b>Tags: </b>'.$videoTags.'</li></ul>';
		$htmlBody .= '<a href="logout.php">Sair</a>';

	  } catch (Google_Service_Exception $e) {
	    $htmlBody .= sprintf('<p>Um erro ocorreu: <code>%s</code></p>',
	        htmlspecialchars($e->getMessage()));
	  } catch (Google_Exception $e) {
	    $htmlBody .= sprintf('<p>Um erro ocorreu: <code>%s</code></p>',
	        htmlspecialchars($e->getMessage()));
		$htmlBody .= 'Resete a sua sessão <a href="logout.php">Saur</a>';
	  }

	  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
	} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
	  $htmlBody = <<<END
  <h3>Credenciais necessária</h3>
  <p>
    Você precisa colocar as suas credencias <code>\$OAUTH2_CLIENT_ID</code> e
    <code>\$OAUTH2_CLIENT_ID</code> antes de prosseguir.
  <p>
END;
	} else {
	  $state = mt_rand();
	  $client->setState($state);
	  $_SESSION['state'] = $state;

	  $authUrl = $client->createAuthUrl();
	  $htmlBody = <<<END
  <h3>Autorização necessária</h3>
  <p>Você precisa autorizar <a href="$authUrl">o acesso</a> antes de prosseguir.<p>
END;
	}

?>
<html>
	<head>
		<title>Sucesso!</title>
		<link rel="stylesheet" type="text/css" href="css/style.css"/>
	</head>
	<body>
		<div class="video-box">
			<h1>Vídeo upado com sucesso</h1>
			<div class="uplink"><a href="<?php echo BASE_URL; ?>">Novo upload</a></div>
			<div class="content">
				<?php echo $htmlBody; ?>
			</div>
		</div>
	</body>
</html>