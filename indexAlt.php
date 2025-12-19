<?php
 $TestColor = "#ff0080";

 if(isset($_REQUEST['tbTestColor'])){
     $saveField = array(
         "TestColor" => $_REQUEST['tbTestColor']
     );
     $saveJson = json_encode($saveField);
     file_put_contents("color.json", $saveJson);
    if(file_exists("color.json")){
        $loaJson = file_get_contents("color.json");
        $loadField = json_decode($loaJson);
        $TestColor = $loadField->TestColor;
    }     
 }
?>
<html>
    <head>
        <title>Color-Test</title>
        <style type="text/css">
            #fieldTestColor{
                background: <?php echo $TestColor; ?>;
            }
            #lgTestColor{
                background: black;
                color: white;
                padding: 5px;   
            }
        </style>
    </head>
    <body>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <fieldset id="fieldTestColor">
                <legend id="lgTestColor">Choose your color</legend>
                <table>
                    <tr><td><label name="lbTestColor" for="tbTestColor">Farbauswahl:</label></td><td><input name="tbTestColor" value="<?php echo $TestColor; ?>" type="color"></td></tr>
                    <tr><td colspan="2"><input type="submit" value="Farbe testen"></td></tr>
                </table>
            </fieldset>
        </form>
    </body>
</html>