<?php
	if(session_id() != ''){
		session_destroy();
	}

	if(isset($_GET['err'])){
		if($_GET['err'] == 'bf'){
			$errorMsg = 'Selecione o vídeo.';
		}elseif($_GET['err'] == 'ue'){
			$errorMsg = 'Desculpe, escolha um arquivo.';
		}elseif($_GET['err'] == 'fe'){
			$errorMsg = 'Formatos aceitos: MP4, AVI, MPEG, MPG, MOV e WMV';
		}else{
			$errorMsg = 'Problemas com o servidor';
		}
	}
?>
<html>
	<head>
		<title>Upload - apenas teste</title>
		<link rel="stylesheet" type="text/css" href="css/style.css"/>
	</head>
	<body>
		<div class="video-box">
			<h1>Upload para o youtube</h1>
			<form method="post" enctype="multipart/form-data" action="upload.php">
				<?php echo (!empty($errorMsg))?'<p class="err-msg">'.$errorMsg.'</p>':''; ?>
				<label for="title">Título:</label><input type="text" name="title" value="" />
				<label for="description">Descrição:</label> <textarea name="description" cols="20" rows="2" style="resize: none;"></textarea>
				<label for="tags">Tags:</label> <input type="text" name="tags" value="" />
				<label for="file">Escolha seu vídeo:</label> <input type="file" name="file" >
				<input name="videoSubmit" type="submit" value="Enviar">
			</form>
		</div>
	</body>
</html>