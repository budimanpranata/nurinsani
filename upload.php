<form enctype="multipart/form-data" action="insert_image.php?id=<?php require_once __DIR__ . '/php8_compat.php'; echo $_GET['id']; ?>" method="post">        
<div id="filediv"><input name="file[]" type="file" id="file"/></div><br/>    
    <input type="button" id="add_more" class="upload" value="Add More Files"/>   
     <input type="submit" value="Upload File" name="submit" id="upload" class="upload"/>    </form>