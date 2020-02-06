<?php
//this is a change
  ini_set('memory_limit', '512M');
  session_start();
  $square_crop  = NULL;
  $banner_crop = NULL;
  $sessionip = $_SERVER['REMOTE_ADDR'];//ip for user
  $sessionid = session_id();//id for the session. transcends load balancing, since stored in cookie.
  //error_reporting(E_ALL);
  //ini_set('display_errors', 1);
  define("EXPORT_FOLDER","/docroot/htdocs/mtu_resources/php/kraken/uploads/");
  define("EXPORT_URL","http://www.mtu.edu/mtu_resources/php/kraken/uploads/");
  define("IMAGE_PREVIEW_SIZE",800);//size of the image when it is displayed to the user.
  define("BENCHMARKING",false);//whether or not to display benchmark timers.
  define("WEB_DAV_ROOT","https://a.cms.omniupdate.com/servlet/dav/oucampus/MTU/www");
  $times = array();//benchmark times.

  //load session information for this client.
  loadSession();

if (isset($_SESSION["webdav_folder"]) && (((strpos($_SESSION["webdav_folder"],"/news") !== false) || (strpos($_SESSION["webdav_folder"],"/news") !== false) || ((strpos($_SESSION["webdav_folder"],"/magazine") !== false) || strpos($_SESSION["webdav_folder"],"/unscripted") !== false)))) {
	//echo strpos($_SESSION["webdav_folder"],"/ou-news");
	echo '<!-- news -->';
	$defaultDimensions = array(
    "170 Square"=>array(
        "width"=>170,
        "height"=>170,
        "constrainWidth"=>true,
        "constrainHeight"=>true,
        "suffix"=>"170sq"
      ),
    "250 Square"=>array(
      "width"=>250,
      "height"=>250,
      "constrainWidth"=>true,
      "constrainHeight"=>true,
      "suffix"=>"250sq"
    ),
    "Personnel Image"=>array(
      "width"=>170,
      "height"=>230,
      "constrainWidth"=>true,
      "constrainHeight"=>true,
      "suffix"=>"personnel"
    ),
    "350 Sidebar"=>array(
      "width"=>350,
      "height"=>200,
      "constrainWidth"=>true,
      "constrainHeight"=>false,
      "suffix"=>"350sidebar"
    ),
    "515 Sub-Banner"=>array(
      "width"=>515,
      "height"=>250,
      "constrainWidth"=>true,
      "constrainHeight"=>false,
      "suffix"=>"515subbanner"
    ),
    "800 Banner"=>array(
      "width"=>800,
      "height"=>325,
      "constrainWidth"=>true,
      "constrainHeight"=>false,
      "suffix"=>"800banner"
    ),
    "1024 Feature"=>array(
      "width"=>1024,
      "height"=>425,
      "constrainWidth"=>true,
      "constrainHeight"=>false,
      "suffix"=>"1024feature"
    ),
    "1600 Feature"=>array(
      "width"=>1600,
      "height"=>520,
      "constrainWidth"=>true,
      "constrainHeight"=>false,
      "suffix"=>"1600feature"
    )
  );
	
} else {

  //information for the default sizes, for information that changes from size to size.
  $defaultDimensions = array(
    "170 Square"=>array(
        "width"=>170,
        "height"=>170,
        "constrainWidth"=>true,
        "constrainHeight"=>true,
        "suffix"=>"170sq"
      ),
    "250 Square"=>array(
      "width"=>250,
      "height"=>250,
      "constrainWidth"=>true,
      "constrainHeight"=>true,
      "suffix"=>"250sq"
    ),
    "Personnel Image"=>array(
      "width"=>170,
      "height"=>230,
      "constrainWidth"=>true,
      "constrainHeight"=>true,
      "suffix"=>"personnel"
    ),
    "350 Sidebar"=>array(
      "width"=>350,
      "height"=>175,
      "constrainWidth"=>true,
      "constrainHeight"=>false,
      "suffix"=>"350sidebar"
    ),
    "515 Sub-Banner"=>array(
      "width"=>515,
      "height"=>250,
      "constrainWidth"=>true,
      "constrainHeight"=>false,
      "suffix"=>"515subbanner"
    ),
    "800 Banner"=>array(
      "width"=>800,
      "height"=>300,
      "constrainWidth"=>true,
      "constrainHeight"=>false,
      "suffix"=>"800banner"
    ),
    "1024 Feature"=>array(
      "width"=>1024,
      "height"=>375,
      "constrainWidth"=>true,
      "constrainHeight"=>false,
      "suffix"=>"1024feature"
    ),
    "1600 Feature"=>array(
      "width"=>1600,
      "height"=>520,
      "constrainWidth"=>true,
      "constrainHeight"=>false,
      "suffix"=>"1600feature"
    )
  );
}
  

  
  //they filled a clean form out 
  if(isset($_POST["image_sizes"]) && isset($_FILES)  && !empty($_FILES)){
      $_SESSION = array();//reset session variables, just in case they didn't finish up their last session.      
      $want_cms_image = isset($_POST["cms_image"]) && !empty($_POST["cms_image"]);//whether the image they want is a cms image.
      //save the folder they were in inside the CMS. This is where we'll be placing the images.
      if(!empty($_POST["site_folder"])){
        if($want_cms_image){
          $_SESSION["webdav_folder"] = preg_replace("/\/images(.*)?$/","$1",$_POST["cms_image"]);
        }
        else{
          $_SESSION["webdav_folder"] = $_POST["site_folder"];
        }
      }
      else{
        die("<br>CMS folder could not be determined. Please ensure you have accessed this page from OUCampus. If the problem persists, pleases contact cmshelp@mtu.edu.");
      }
      if($want_cms_image){
        if(isset($_POST["title"]) && !empty($_POST["title"])){
          $title = $_POST["title"];
          $extension  = preg_replace("/(.*).(jpg|png)$/","$2",$_POST["cms_image"]);
          if($extension !== "jpg" && $extension !== "png"){
            die("Invalid file type. Only jpg and png files are allowed.");
          }
          $success = preg_match('/^[a-z0-9-\.]+$/',$title,$matches);
          if($success){
            $_SESSION["filename"] = "$title.$extension";
          }
          else{
            die("Invalid filename was provided. Filenames should have only lower case leters, numbers, hyphens, and periods.");
          }
        }
        else{
            die("Invalid filename was provided. Filenames should have only lower case leters, numbers, hyphens, and periods.");
        }
      } 
      else{
        //check the file type. Only JPG and PNG supported.
		  //print_r($_FILES);
        $type = @exif_imagetype($_FILES['file']['tmp_name']);
        if($type){
          if($type == IMAGETYPE_JPEG){
            $extension = "jpg";
          }
          else if($type == IMAGETYPE_PNG){
            $extension = "png";

          }
          else{
            die("Invalid file type. Only jpg and png files are allowed.");
          }
        }
        else{
          die("File type could not be determined. File may be corrupt or invalid.");
        }

        if(isset($_POST["title"]) && !empty($_POST["title"])){
          $title = $_POST["title"];
          $success = preg_match('/^[a-z0-9-\.]+$/',$title,$matches);
          if($success){
            $_SESSION["filename"] = "$title.$extension";
          }
          else{
            die("Invalid filename was provided. Filenames should have only lower case leters, numbers, hyphens, and periods.");
          }
        }
        else{
          $_SESSION["filename"] = preg_replace("/[^a-z0-9\.-]/g","",preg_replace("/[_\s]/g","-",strtolower($_FILES['file']['name']))).".";
        }
      }
    
      //make sure we have a unique filename, so we don't have to worry about handling duplicates    
      while(file_exists(EXPORT_FOLDER.md5($_SESSION["filename"]))){
        $_SESSION["filename"] = $_SESSION["filename"].rand();
      }
      $save_path = EXPORT_FOLDER.md5($_SESSION["filename"]);
      if($want_cms_image){
          //webdav user information
          $username = "user9";
          $password = "user9";
          //create the images directory (if it doesn't exist)
          $url=WEB_DAV_ROOT.$_POST["cms_image"];//the url we'll be uploading to.
          $ch = curl_init(str_replace(" ","%20",$url));
          curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
          curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);//do ssl verify
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $response = curl_exec($ch);
          file_put_contents($save_path,$response);
      }
      else{
        move_uploaded_file($_FILES['file']['tmp_name'], $save_path);
      }
                     
      $size = getimagesize($save_path);
      unset($size[3]);
      $_SESSION["original_size"] = $size;
      $_SESSION["current_stage"] = 0;
      $_SESSION["sizes"] = $_POST["image_sizes"];
      $_SESSION["originalpath"] = $save_path;
      $_SESSION["originalurl"] = EXPORT_URL.md5($_SESSION["filename"]);
      $_SESSION["selection"] = array();
      $_SESSION["crop"] =array();
      $_SESSION["square-crop"] = NULL;
      $_SESSION["banner-crop"] = NULL;
      $_SESSION["visited"] = array();
      croppingScreen();
  }
  //have they already started a cropping session? Also verify they authorized at some point.
  else if(isset($_SESSION["current_stage"]) && isset($_SESSION["sizes"]) && isset($_SESSION["originalurl"])){

    //clicked exit
    if(isset($_POST["exit"])){
      clearSession();
      echo <<< CLOSESCRIPT
      <script type = "text/javascript">
        window.opener.CloseEditor();
      </script>
CLOSESCRIPT;
    }
    //clicked confirm
    else if(isset($_POST["confirm"])){
      ob_start();
      $images = cropImages();
       save_image_info($images);
	//print_r($images);
      $optimized = optimizeImages($images);
      if(!empty($optimized)){
        sendImages($optimized);
        echo "<div style=\"padding-bottom: 1em\">The image has been cropped and added into OU Campus in the folder ".dirname($_SESSION["webdav_folder"])."/images/</div>";
        clearSession();
      }

      echo <<< CLOSESCRIPT
      <!doctype html>
      <html lang="en">
      <!--[if (!IE)|(gt IE 8)]><!-->
      <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
      <script>window.jQuery || document.write('<script src="//www.mtu.edu/mtu_resources/script/n/jquery.js"><\/script>')</script>
      <!--<![endif]-->
      <!--[if lte IE 8]>
        <script src="//www.mtu.edu/mtu_resources/script/n/jquery-ie.js"></script>
      <![endif]-->
      <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
      <body style="padding: 1em">
        <form>
          <button class="btn btn-primary" id = "exit-button">Exit</button>
        </form>
        <script>
          $("#exit-button").click(function(e){
            e.preventDefault();
            var form = window.opener.document.getElementById("image-upload-form");
            form.reset();
            $(form).find("#cms_image").val("");
            window.opener.CloseEditor();
          });
          window.addEventListener("beforeunload", function(e){
            var form = window.opener.document.getElementById("image-upload-form");
            form.reset();
            $(form).find("#cms_image").val("");
            window.opener.CloseEditor();
          }, false);
		  
		  function closeWindow() {
			setTimeout(function() {
				var form = window.opener.document.getElementById("image-upload-form");
				form.reset();
				$(form).find("#cms_image").val("");
				window.opener.CloseEditor();
			}, 15000);
		  }

    window.onload = closeWindow();
	
        </script>
        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="//a.cms.omniupdate.com/10/style.css" />
  <style>
    label.error{
      color:red;
    }
	.image-wrap {
		margin: 0 12px 12px 12px;
	}
	#fileUpload {
		display: none;
	}
	.form-group {
		margin-bottom: 1em;
	}
	input[type="checkbox"] + label {
		display: inline-block;
		margin-left: .5em;
		position: relative;
		top: 3px;
	}
	fieldset {
		padding: .8em;
		border: 1px solid #e2e2e2;
	}
	legend {
		font-size: 14px;
		font-weight: bold;
		margin-bottom: 0;
	}
	p {
		margin-bottom: 1em;
	}
	input[type="text"] {
		margin: 0;
	}
  </style>
      </body>
      </html>
CLOSESCRIPT;
      
      if(BENCHMARKING){
        echo "<br><strong>Time (in seconds)</strong><br>";
        echo "<pre>".print_r($times,true)."</pre>";
      }
      ob_flush();
    }
    
    //TODO: Fix minor bugs here.
    //they're navigating through crops
    else if((isset($_POST["previous"]) || isset($_POST["next"])) && isset($_POST["stage"]) && $_POST["stage"] == $_SESSION["current_stage"]){
      //save the crop values they just put in
      if(isset($_POST["selection"])){
       
        
        if(($_POST["size"]=="170 Square")or($_POST["size"]=="250 Square")){
          $_SESSION["square-crop"] = json_decode($_POST["selection"],true);
     
        }
        
        if(($_POST["size"]=="515 Sub-Banner")or($_POST["size"]=="800 Banner")or($_POST["size"]=="1024 Feature")or($_POST["size"]=="1600 Feature")){
          $_SESSION["banner-crop"] = json_decode($_POST["selection"],true);
     
        }
        $_SESSION["selection"][$_SESSION["current_stage"]] = json_decode($_POST["selection"],true);
        $_SESSION["crop"][$_SESSION["current_stage"]] = json_decode($_POST["crop"],true);
           
        if(isset($_POST["constrain"]) && !empty($_POST["constrain"])){
          $_SESSION["constrain"][$_SESSION["current_stage"]] = true;
        }
        else{
          $_SESSION["constrain"][$_SESSION["current_stage"]] = false;
        }
      }else{
        //if we didn't come from the confirmation stage, then we 
        if($_POST["stage"] == count($_SESSION["sizes"])){
          echo"<p>The crop you entered was invalid</p>";
        }
        croppingScreen();
        exit;
      }
      if(isset($_POST["previous"]) && $_SESSION["current_stage"] > 0){
        $_SESSION["current_stage"]--;
        croppingScreen();
      }
      else if(isset($_POST["next"])){
       
        //$_SESSION["current_stage"]++;
        if($_SESSION["current_stage"] >= count($_SESSION["sizes"])-1){
          confirmationScreen();
        }
        else{
          $_SESSION["current_stage"]++;
          croppingScreen();
        }
      }
      else{
        echo "<p>There was an issue with your crop, please try recropping.</p>";
        croppingScreen();
      }
    }else{
        if($_SESSION["current_stage"] >= count($_SESSION["sizes"])-1 && isset($_POST["stage"]) && $_POST["stage"] == $_SESSION["current_stage"]){
                   confirmationScreen();
        }else{
          croppingScreen();
        }
    }
  }
  else{
    //include "index.php";
   
    echo "Something went wrong, please try again within OUCampus";
  }

##
# Function for printing the cropping screen of the image editor.
# This includes code for making the cropping, and the javascript necessary to create the preview imagees.
##
  function croppingScreen(){
    global $defaultDimensions;
 

      $name = pathinfo($_SESSION["filename"],PATHINFO_FILENAME)."-".$defaultDimensions[$_SESSION["sizes"][$_SESSION["current_stage"]]]["suffix"]. ".jpg";
    $ou_img_path = dirname($_SESSION["webdav_folder"])."/images/".$name;
  
 $x1 = $x2 = $y1 = $y2 = -1;
 $code = $_SESSION["sizes"][$_SESSION["current_stage"]];
     if ($_SESSION["visited"][$_SESSION["current_stage"]] != true){
        $con = mysqli_connect("mysql.ais.mtu.edu","umcwebuser","umcpass01","umcwebgen");
   $query  = "SELECT * FROM ou_imed_crops WHERE path = \"$ou_img_path\" AND img_code = \"$code\"";
      $result = array();
      $result   = mysqli_query($con,$query);
  while($row =  mysqli_fetch_assoc($result)) {
   if ($row["path"] != ""){
    $x1 = $row["x1"];
    $x2 = $row["x2"];
    $y1 = $row["y1"];
    $y2 = $row["y2"];
   }
   break; 
  }
}
  $old_selection = array(
'x1' => $x1,
'x2' => $x2,
'y1' => $y1,
'y2' => $y2
    );
  $old_selection = json_encode($old_selection);

  $_SESSION["visited"][$_SESSION["current_stage"]] = true;
    saveSession();//when we get to the point where we're displaying things, all the session information is done being changed, so save the current state.
    $size = $_SESSION["sizes"][$_SESSION["current_stage"]];///the name of the size currenly being cropped.
    if(isset($_SESSION["square-crop"])) {
  
      $square_crop = $_SESSION["square-crop"];
    } else {
      $square_crop = array();
    }
    if(isset($_SESSION["banner-crop"])) {
      $banner_crop = $_SESSION["banner-crop"];
    } else {
      $banner_crop = array();
    }
    //if they already have a previous saved selection for this cropping, use it.
    if(isset($_SESSION["selection"][$_SESSION["current_stage"]]) && !empty($_SESSION["selection"][$_SESSION["current_stage"]])){
      $selection = $_SESSION["selection"][$_SESSION["current_stage"]];
    }
    else{
      $selection = array();
    }
    ?>
    <!doctype html>
    <html lang="en">
        <head>
          <title></title>
          <!--[if (!IE)|(gt IE 8)]><!-->
          <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
          <script>window.jQuery || document.write('<script src="//www.mtu.edu/mtu_resources/script/n/jquery.js"><\/script>')</script>
          <!--<![endif]-->
          <!--[if lte IE 8]>
            <script src="//www.mtu.edu/mtu_resources/script/n/jquery-ie.js"></script>
          <![endif]-->
          <link rel="stylesheet" type="text/css" href="imgareaselect/css/imgareaselect-default.css" />
          <script src = "imgareaselect/scripts/jquery.imgareaselect.pack.js"></script>
          <script>
          </script>
          <style>
            #crop-image{
              max-width:<?php echo IMAGE_PREVIEW_SIZE; ?>px;
            }
            #preview-image{
              <?php
                //fix dimensions on the preview image, so that it reflects how things will actually be cropped.
                if($defaultDimensions[$size]["constrainWidth"]){
                  echo "width:".$defaultDimensions[$size]["width"]."px;\n";
                }
                if($defaultDimensions[$size]["constrainHeight"]){
                  echo "height:".$defaultDimensions[$size]["height"]."px;\n";
                }
              ?>
              overflow:hidden;
              position:relative
            }
          </style>
          <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="//a.cms.omniupdate.com/10/style.css" />
  <style>
    label.error{
      color:red;
    }
	.image-wrap {
		margin: 0 12px 12px 12px;
	}
	#fileUpload {
		display: none;
	}
	.form-group {
		margin-bottom: 1em;
	}
	input[type="checkbox"] + label {
		display: inline-block;
		margin-left: .5em;
		position: relative;
		top: 3px;
	}
	fieldset {
		padding: .8em;
		border: 1px solid #e2e2e2;
	}
	legend {
		font-size: 14px;
		font-weight: bold;
		margin-bottom: 0;
	}
	p {
		margin-bottom: 1em;
	}
	input[type="text"] {
		margin: 0;
	}
	
	.margin-left {
		margin-left: .5em;
	}
	.body-pad {
		padding: 1em;
	}
	.constrain {
		display: inline-block;
	}
	.constrain + input[type="checkbox"] {
		top: -3px;
		position: relative;
		margin-left: .5em;
	}
  input[type="number"]{
    height:auto;
  }
	#preview-image {
		margin-bottom: 1em;
	}
  #preview-image img{
    max-width:none;
  }
  </style>
        </head>
        <body class="body-pad">
          <h1><?php echo $_SESSION["sizes"][$_SESSION["current_stage"]];?></h1>
          <p>Perform a crop on the image</p>

          <form method="post">
           
              <div><label for="custom-width" >Display width</label><input type = "number" min="0" id = "custom-width" value="<?php echo (isset($selection["width"])?$selection["width"]:""); ?>"></div>
              <div><label for="custom-height">Display height</label><input type = "number" min="0" id = "custom-height" value="<?php echo (isset($selection["height"])?$selection["height"]:""); ?>"></div>
              <div><label for="constrain" class="constrain">Constrain aspect?</label><input type = "checkbox" id = "constrain" name = "constrain" value = "constrain" 
              <?php if($defaultDimensions[$size]["constrainWidth"] && $defaultDimensions[$size]["constrainHeight"] && !isset($_SESSION["constrain"][$_SESSION["current_stage"]])){ echo "checked";} else if(isset($_SESSION["constrain"][$_SESSION["current_stage"]]) && $_SESSION["constrain"][$_SESSION["current_stage"]]){echo "checked";} ?>></div>
            <div class="form-group">
            <button id = "recommended-dims" class="btn">Use Recommended Aspect</button>
            <button class="btn margin-left" id = "whole-image">Select Whole Image</button>
            <?php 
            if (($size=="170 Square")or($size=="250 Square")){
              if(!empty($square_crop)){
                ?>
                <button id="use-previous" class="btn">Use Square Crop</button>
                <?php
              }
            }
            if (($size=="515 Sub-Banner")or($size=="800 Banner")or($size=="1024 Feature")or($size=="1600 Feature")){
              if(!empty($banner_crop)){
                ?>
                <button id="use-previous" class="btn">Use Banner Crop</button>
                <?php
              }}
            ?>
            </div>
         
              <div><img id = "crop-image" src = "<?php echo $_SESSION["originalurl"]?>"></div>
              <h2>Preview</h2>
              <div id ="preview-image"><img style="position:relative;" src = "<?php echo $_SESSION["originalurl"]?>"></div>
            <?php if($_SESSION["current_stage"] > 0){?><input value = "Previous" type = "submit" class="btn" name = "previous"/><?php }?><input class="btn btn-primary margin-left" value = "Next" type = "submit" name = "next"><input value = "Exit" type = "submit" name = "exit" class="btn margin-left"/>
         
            <input id="size" type = "hidden" name="size" value="<?php echo $_SESSION["sizes"][$_SESSION["current_stage"]]?>">
           <input type = "hidden" name = "stage" value = "<?php echo $_SESSION["current_stage"] ?>">
            <input type = "hidden" id = "crop" name = "crop" <?php if(isset($y2)){echo "value=\"{}\"";} ?>>
            <input type = "hidden" id = "selection" name ="selection"  <?php if(isset($selection)){echo "value='".json_encode($selection)."'";} ?>>
            <input type = "hidden" id = "square-crop" name ="square-crop"  <?php if(isset($square_crop)){echo "value='".json_encode($square_crop)."'";} ?>>
            <input type = "hidden" id = "banner-crop" name ="banner-crop"  <?php if(isset($banner_crop)){echo "value='".json_encode($banner_crop)."'";} ?>>
             <input type = "hidden" id = "db-crop" name ="db-crop"  <?php if(isset($old_selection)){echo "value='".$old_selection."'";} ?>>
          </form>
           
          
          <script>
            var size = $("input#size").val();
              var selection = JSON.parse($("#selection").val());//parse their last selection, if they had one.
             var square = JSON.parse($("#square-crop").val());
              var banner = JSON.parse($("#banner-crop").val());
              var db_crop = JSON.parse($("#db-crop").val());
               if(db_crop.x1 >-1 && db_crop.x2 > -1 && db_crop.y1 > -1 && db_crop.y2 > -1){
                selection = db_crop;
               }
              var ias;//current instance of imgareaselect
              var cropimage = $("#crop-image");//the image they are cropping
              var imgWidth,imgHeight;//display height and width for the image being cropped.

              //if they previously had a valid selection, use that
              if(selection.x1 >-1 && selection.x2 > -1 && selection.y1 > -1 && selection.y2 > -1){
                ias = $("#crop-image").imgAreaSelect({
                  handles:true,
                  onSelectEnd: updateExternal,
                  x1:selection.x1,
                  x2:selection.x2,
                  y1:selection.y1,
                  y2:selection.y2,
                  instance:true,
                  keys:true,
                  onInit: function(){
                    //set image size for reference later.
                    imgWidth = cropimage.width();
                    imgHeight = cropimage.height();
                    
                    //if their previous crop had problems, revert to recommended dimensions
                    var sel = ias.getSelection();
                    if(isNaN(sel["x1"]) || isNaN(sel["y1"]) || isNaN(sel["x2"]) || isNaN(sel["y2"])){
                      setRecDims();
                    }
                    //initialize the preview and width/height boxes
                    updateExternal(ias,ias.getSelection());
                    //initialize our aspect settings
                    aspectCheck();
                  }
                });
              }
              //they didn't have a valid selection before, so just setup a selection with the recommended dimensions
              else{
                ias = $("#crop-image").imgAreaSelect({
                  handles:true,
                  onSelectEnd: updateExternal,
                  x1:0,
                  x2:0,
                  y1:0,
                  y2:0,
                  keys:true,
                  instance:true,
                  onInit: function(){
                    //set image size for reference later.
                    imgWidth = cropimage.width();
                    imgHeight = cropimage.height();
                    
                    //begin crop as recommended dimensions
                    setRecDims();
                    //initialize the preview and width/height boxes
                    updateExternal(ias,ias.getSelection());
                    //initialize our aspect settings
                    aspectCheck();
                  }
                });
              }
                         //when they change the display width/height boxes, reflect this in the crop
              $("#custom-height,#custom-width").change(function(changed){
                var newWidth =parseInt($("#custom-width").val());
                var newHeight =parseInt($("#custom-height").val());
                selection = ias.getSelection();
                //update the crop, and our preview of it based on the change they made.
                ias.setSelection(selection.x1,selection.y1,selection.x1+newWidth,selection.y1+newHeight);
                ias.update();
                updateExternal(ias,ias.getSelection());
              });
            
              //update ias aspect when checkbox toggled
              $("#constrain").change(function(){
                aspectCheck();
              });
            
              //if the "use recommended aspect" button is clicked, switch to recommended dimensions, and constrain
              $("#recommended-dims").click(function(e){
                e.preventDefault();
                setRecDims();
                updateExternal(ias,ias.getSelection());
                $("#constrain").prop("checked",true);
                aspectCheck();
              });
            
              //if the select whole image button is clicked, select the whole image.
              $("#whole-image").click(function(e){
                e.preventDefault();
                ias.setSelection(0,0,imgWidth,imgHeight);
                updateExternal(ias,ias.getSelection());
                ias.update();
                $("#constrain").prop("checked",false);
                aspectCheck();
              })
               $("#use-previous").click(function(e){
              e.preventDefault();
              if((size=="170 Square")||(size=="250 Square")){
                var saveCrop = square;
              }
              if((size=="515 Sub-Banner")||(size=="800 Banner")||(size=="1024 Feature")||(size=="1600 Feature")){
                var saveCrop = banner;
              }
             
                ias.setSelection(saveCrop.x1,saveCrop.y1,saveCrop.x2,saveCrop.y2);
                updateExternal(ias,ias.getSelection());
                ias.update();
             
              })
              //function used to keep the text boxes and preview up to date with user's selection
              function updateExternal(img, selection){
                preview(img,selection);
                $("#custom-width").val(selection.width);
                $("#custom-height").val(selection.height);
                $("#selection").val(JSON.stringify(selection));
              }
            
              //function to update the preview image.
              function preview(img, selection) {
                      <?php 
                    //we have an exact size width, height, or both that we will be cropping to.
                    if($defaultDimensions[$size]["constrainWidth"] && $defaultDimensions[$size]["constrainHeight"]){
                  ?>
                      var scaleX = <?php echo $defaultDimensions[$size]["width"] ?> / (selection.width || 1);
                      var scaleY = <?php echo $defaultDimensions[$size]["height"] ?>/ (selection.height || 1);
                  <?php }elseif($defaultDimensions[$size]["constrainWidth"]){ ?>
                      var scaleX = <?php echo $defaultDimensions[$size]["width"] ?> / (selection.width || 1);
                      var scaleY = scaleX;
                      var heightlim = Math.round(selection.height *scaleY);
                      $("#preview-image").css({
                          height: heightlim
                      });
                  <?php }elseif($defaultDimensions[$size]["constrainHeight"]){ ?>
                      var scaleY = <?php echo $defaultDimensions[$size]["height"] ?>/ (selection.height || 1);
                      var scaleX = scaleY;
                      var widthlim = Math.round(selection.width *scaleX);
                      $("#preview-image").css({
                          width: widthlim
                      });
                  <?php }?>
                      //variable storing values to make the preview, and help with doing the crop on the backend.
                      var crop = {
                        "width":scaleX * imgWidth,
                        "height":scaleY * imgHeight,
                        "x":scaleX * selection.x1,
                        "y":scaleY * selection.y1,
                      };
                      //whether or not we should set a limit on the width or height, or they are unconstrained.
                      if( typeof heightlim != "undefined"){
                        crop.heightlim = heightlim;
                      }
                      else if(typeof widthlim != "undefined"){
                        crop.widthlim = widthlim;
                      }
                      //save the crop settings, so the backend can use them later.
                      $("#crop").attr("value",JSON.stringify(crop));
                      //set styles to update the preview.
                      $('#preview-image > img').css({
                          width:  crop["width"]+ 'px',
                          height: crop["height"] + 'px',
                          marginLeft: '-' + crop["x"] + 'px',
                          marginTop: '-' + crop["y"] + 'px'
                      });
              }
              
              //toggle fixed aspect ratio based on checkbox
              function aspectCheck(){
                if($("#constrain").is(':checked')){
                  $("#custom-width").prop("disabled",true);
                  $("#custom-height").prop("disabled",true);
                  var width = ias.getSelection().width;
                  var height = ias.getSelection().height;
                  ias.setOptions({"aspectRatio":width+":"+height});
                }
                else{
                  $("#custom-width").prop("disabled",false);
                  $("#custom-height").prop("disabled",false);
                  ias.setOptions({"aspectRatio":false});
                }
              }
            
              //set user selection to our recommended dimensions.
              function setRecDims(){
                <?php

                  $aspect = $defaultDimensions[$size]["width"] / $defaultDimensions[$size]["height"];
                ?>
                var aspect = imgWidth/imgHeight;
                if(aspect >=<?php echo $aspect; ?>){
                  ias.setSelection(0,0,imgHeight*<?php echo $aspect; ?>,imgHeight);
                }
                else{
                  ias.setSelection(0,0,imgWidth,imgWidth/<?php echo $aspect; ?>);
                }
                ias.update();
              }
          </script>
        </body>
      </html>
    <?php

  }
  
  //function to create the confirmation screen.
  function confirmationScreen(){
    global $defaultDimensions;
    saveSession();
    ?>
    <!doctype html>
    <html lang="en">
        <head>
          <title></title>
          <!--[if (!IE)|(gt IE 8)]><!-->
          <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
          <script>window.jQuery || document.write('<script src="//www.mtu.edu/mtu_resources/script/n/jquery.js"><\/script>')</script>
          <!--<![endif]-->
          <!--[if lte IE 8]>
          <script src="//www.mtu.edu/mtu_resources/script/n/jquery-ie.js"></script>
          <![endif]-->
          <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="//a.cms.omniupdate.com/10/style.css" />
  <style>
    label.error{
      color:red;
    }
	.image-wrap {
		margin: 0 12px 12px 12px;
	}
	#fileUpload {
		display: none;
	}
	.form-group {
		margin-bottom: 1em;
	}
	input[type="checkbox"] + label {
		display: inline-block;
		margin-left: .5em;
		position: relative;
		top: 3px;
	}
	fieldset {
		padding: .8em;
		border: 1px solid #e2e2e2;
	}
	legend {
		font-size: 14px;
		font-weight: bold;
		margin-bottom: 0;
	}
	p {
		margin-bottom: 1em;
	}
	input[type="text"] {
		margin: 0;
	}
	.body-pad {
		padding: 1em;
	}
	.margin-left {
		margin-left: .5em;
	}
	.margin-top {
		margin-top: 1em;
	}
  .preview img{
    max-width:none;
  }
  </style>
       
<script type="text/javascript">
		
	$(document).ready(function () {
		$("#confirm-button").click(function () {
			setTimeout(function () { disableButton(); }, 0);
		});

		function disableButton() {
			$("#confirm-button").attr('disabled', 'disabled');
			$("#confirm-button").attr('value', 'Processing . . .');
		}
	});
</script>	   
        </head>
        <body  class="body-pad">
          <h1>Confirmation Screen</h1>
          <p>Please confirm that the croppings you have made are correct.</p>
          <?php
            foreach($_SESSION["selection"] as $index =>$crop){
              $size = $_SESSION["sizes"][$index];
              echo "<strong>".$size."</strong>";
              if(isset($_SESSION["crop"][$index])){
                $defaults = $defaultDimensions[$size];
                $crop = $_SESSION["crop"][$index];
                $divStyles='style="position:relative;overflow:hidden;'.($defaults["constrainWidth"]?"width:".$defaults["width"]."px;":"width:".$crop["widthlim"]."px;").($defaults["constrainHeight"]?"height:".$defaults["height"]."px;":"height:".$crop["heightlim"]."px;").'"';
                echo '<div class="preview" '.$divStyles.'>';
                $imgStyles = 'style="width:'.$crop["width"].'px;height:'.$crop["height"].'px;margin-left:-'.$crop["x"].'px;margin-top:-'.$crop["y"].'px"';
                echo '<img src="'.$_SESSION["originalurl"].'"'.$imgStyles.' "/>';
                echo '</div>';
              }
            }
          ?>
          <form id="confirm" method="post">
            <div class="form-group margin-top"><input id="thebutton" value = "Previous" type = "submit" name = "previous" class="btn" /><input value = "Exit" class="btn margin-left" type = "submit" name = "exit"/><input id="confirm-button" class="btn margin-left btn-primary" value = "Confirm" type = "submit" name = "confirm"/></div>
            <input type = "hidden" name = "stage" value = "<?php echo $_SESSION["current_stage"] ?>">
          </form>
        </body>
      </html>
    <?php
  }
  
  function cropImages(){
    global $defaultDimensions, $times;
    $startTime = microtime(true);
    switch ($_SESSION["original_size"][2]) {
      case IMAGETYPE_PNG:
        $source = imagecreatefrompng($_SESSION["originalpath"]);
        break;
      case IMAGETYPE_JPEG:
        $source = imagecreatefromjpeg($_SESSION["originalpath"]);
        break;
    }
    //buffer output, so we can clear session after we have everything figured out.
    //crop the images
    $tmp_files = array();
    //crop the images and get their curl handlers setup for kraken.
    foreach($_SESSION["crop"] as $index=>$crop){
      //the demensions we are looking to crop to
      if($defaultDimensions[$_SESSION["sizes"][$index]]["constrainWidth"] && $defaultDimensions[$_SESSION["sizes"][$index]]["constrainHeight"]){
        $temp_width =$defaultDimensions[$_SESSION["sizes"][$index]]["width"];
        $temp_height = $defaultDimensions[$_SESSION["sizes"][$index]]["height"];
      }
      else if($defaultDimensions[$_SESSION["sizes"][$index]]["constrainWidth"]){
        $temp_width =$defaultDimensions[$_SESSION["sizes"][$index]]["width"];
        $temp_height = $_SESSION["crop"][$index]["heightlim"];

      }
      else if($defaultDimensions[$_SESSION["sizes"][$index]]["constrainHeight"]){
        $temp_height = $defaultDimensions[$_SESSION["sizes"][$index]]["height"];
        $temp_width = $_SESSION["crop"][$index]["widthlim"];
      }

      //create the cropped version
      $temp_image = imagecreatetruecolor($temp_width, $temp_height);
      imagefill($temp_image,0,0,imagecolorallocate($temp_image,255,255,255));
      imagecopyresampled($temp_image, $source, -$crop["x"], -$crop["y"], 0, 0, $crop["width"], $crop["height"], $_SESSION["original_size"][0], $_SESSION["original_size"][1]);
      //dump into a temporary file. We don't really need this file, it is just an intermediary
      $tmp = tmpfile();
      $tmp_files[$index]=$tmp;
      $path = stream_get_meta_data($tmp);
      imagejpeg($temp_image,$path["uri"],100);
      imagedestroy($temp_image);
    }
    if(BENCHMARKING){
      $times["Cropping"] = microtime(true) - $startTime;
    }
    return $tmp_files;
  }


  function optimizeImages($images){
      global $times;
      $startTime = microtime(true);
      $mh = curl_multi_init();
      $handles = array();
      $params = array();
    
	  //var_dump($images);
    
	  // if statement for checking kraken http status
	  if(file_get_contents('http://www.mtu.edu/mtu_resources/php/cms/getHttps/status.php?url=http://kraken.io') != 0) {
        //setup the curl handles.
		 
		foreach($images as $index => $image){
        $path = stream_get_meta_data($image);
        $path = $path["uri"];
        $params[$index] = array(
          "wait"=>true,
          "lossy" => true,
		  "quality" => 85,
		  // https://kraken.io/docs/chroma-subsampling 	
		  "sampling_scheme" => "4:2:0",
          //"dev"=>true
        );
        $auth = array("auth"=>array("api_key"=>"3199feb46546ed47c1205009112a48e8", "api_secret"=>"5e6431cc391aebd108bc2ffbddb3672e0736e225"));
			  $file = '@' . $path;
        $data = array_merge(array(
          "file"=>$file,
          "data"=>json_encode(array_merge($auth,$params[$index]))
        ));
        $url = 'https://api.kraken.io/v1/upload';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        $handles[$index] = $ch;
        curl_multi_add_handle($mh,$ch);
        //break;
      }
      
      //perform the curls.
      $active = null;
      //execute the handles
      do {
          $mrc = curl_multi_exec($mh, $active);
      } 
      while ($mrc == CURLM_CALL_MULTI_PERFORM);

     /* while ($active && $mrc == CURLM_OK) {
          if (curl_multi_select($mh) != -1) {
              do {
                  $mrc = curl_multi_exec($mh, $active);
              } while ($mrc == CURLM_CALL_MULTI_PERFORM);
          }
      } */
		 
		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($mh) == -1) usleep(10);
			do { $mrc = curl_multi_exec($mh, $active); }
			while ($mrc == CURLM_CALL_MULTI_PERFORM);
		} 
		 
     // curl_multi_close($mh);
    
      //$mh = curl_multi_init();
      //get all the urls for optimized images.
      foreach($handles as $index=>$ch){
        unset($response);
        $response = json_decode(curl_multi_getcontent($ch),true);        
        curl_multi_remove_handle($mh,$ch);
        
        $imageUrl = $response["kraked_url"];
		echo "<!-- ". $imageUrl. " -->";
        $img_ch = curl_init();
        curl_setopt($img_ch, CURLOPT_URL, str_replace(" ","%20",$imageUrl));
        curl_setopt($img_ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($img_ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($img_ch, CURLOPT_FOLLOWLOCATION, true);
        $handles[$index] = $img_ch;
        curl_multi_add_handle($mh,$img_ch);
      }

          //perform the curls.
      $active = null;
      //execute the handles
      do {
          $mrc = curl_multi_exec($mh, $active);
      } 
      while ($mrc == CURLM_CALL_MULTI_PERFORM);

      while ($active && $mrc == CURLM_OK) {
         if (curl_multi_select($mh) == -1) usleep(10);
              do {
                  $mrc = curl_multi_exec($mh, $active);
              } while ($mrc == CURLM_CALL_MULTI_PERFORM);
          //}
      }
      //get all the optimized images and write them back to our tempfiles.
      $optimized = array();
      foreach($handles as $index=>$ch){
        unset($response);
        $response = curl_multi_getcontent($ch);
        $path = stream_get_meta_data($images[$index]);
		
        $path = $path["uri"];
		//echo $path; 
		$path = str_replace("/","-",$path);
		file_put_contents('/docroot/htdocs/mtu_resources/php/kraken/kraked/'.$path.'.jpg', $response);  
        file_put_contents($path,$response);
        clearstatcache();
        $optimizedImages[$index] = $images[$index];
        curl_multi_remove_handle($mh,$ch);
      }
    
    
      curl_multi_close($mh);
      if(BENCHMARKING){
        $times["Optimizing"] = microtime(true) - $startTime;
      }
		  
	  }//end if statement for checking kraken http status
	  
      return $images;
  }

  function sendImages($images){
    global $defaultDimensions,$times;
    $startTime = microtime(true);
    //webdav user information
    $username = "user9";
    $password = "user9";
    //create the images directory (if it doesn't exist)
    $url=WEB_DAV_ROOT.dirname($_SESSION["webdav_folder"])."/images/";//the url we'll be uploading to.
    $ch = curl_init(str_replace(" ","%20",$url));
    curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);//do ssl verify
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "MKCOL");
    $response = curl_exec($ch);
	/*$info = curl_getinfo($ch);  
	print_r($info); */ 

    
    $mh = curl_multi_init();
    $handles = array();
    $original = fopen($_SESSION["originalpath"],"r");
    $images["original"] = $original;
    foreach($images as $index=>$image){
      if($index !=="original"){
        $name = pathinfo($_SESSION["filename"],PATHINFO_FILENAME)."-".$defaultDimensions[$_SESSION["sizes"][$index]]["suffix"]. ".jpg";
      }else{
        $name = pathinfo($_SESSION["filename"],PATHINFO_FILENAME)."-orig.jpg";
      }
      $path = stream_get_meta_data($images[$index]);
      $path = $path["uri"];
	  if(strpos($path,"/tmp") !== false) {
		  $path = '/docroot/htdocs/mtu_resources/php/kraken/kraked/'.str_replace("/","-",$path).'.jpg';
	  }
	
      $ch = curl_init(str_replace(" ","%20",$url.$name));
	  //echo str_replace(" ","%20",$url.$name);
      curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);//do ssl verify
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
      curl_setopt($ch, CURLOPT_PUT, 1);//need to do put request to upload a file.
	  $filehandle = fopen($path, 'r');
	  echo filesize($path);
      curl_setopt($ch, CURLOPT_INFILE, $filehandle);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_INFILESIZE, filesize($path));
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));
      $handles[$index] = $ch;
      curl_multi_add_handle($mh,$ch);
    }
    
    $active = null;
    do {
        $mrc = curl_multi_exec($mh, $active);
    } 
    while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active && $mrc == CURLM_OK) {
     
		if (curl_multi_select($mh) == -1) usleep(10);
		
            do {
              $mrc = curl_multi_exec($mh, $active);
              $info = curl_multi_info_read($mh);
              $ch = $info["handle"];
              //upload failed because file was locked
              if($ch && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 423){
                $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                echo "Warning: The file ".basename($url)." could not be uploaded, because the image is currently checked out. <br />";
              }
              //wasn't a successful transfer for some other reason.
              else if($ch && curl_getinfo($ch, CURLINFO_HTTP_CODE) < 200 || $ch && curl_getinfo($ch, CURLINFO_HTTP_CODE) > 299){
                $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); 
                echo "Waring: The file ".basename($url). " could not be uploaded: ".curl_error($ch)." If the problem persists, please contact cmshelp@mtu.edu<br>";
              }
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        //}
    }
    
    foreach($handles as $index => $ch){
      curl_multi_remove_handle($mh,$ch); 
    }	  
	  
    foreach($images as $index=>$image){
      fclose($image);
	  if($index !=="original"){
        $name = pathinfo($_SESSION["filename"],PATHINFO_FILENAME)."-".$defaultDimensions[$_SESSION["sizes"][$index]]["suffix"]. ".jpg";
      }else{
        $name = pathinfo($_SESSION["filename"],PATHINFO_FILENAME)."-orig.jpg";
      }
	  
		
	  //Publish files here... correct path to image below
	  $ou_img_path = dirname($_SESSION["webdav_folder"])."/images/".$name;
	  $imgpostvals = array('site' => 'www', 'path' => $ou_img_path, 'target' => 'www');
	  $authpostvals = array('skin' => 'oucampus', 'account' => 'MTU', 'username' => 'user9', 'password' => 'user9');
	  
	 
	  $url='https://a.cms.omniupdate.com/authentication/login';
	  $ch = curl_init(str_replace(" ","%20",$url));
	  //curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
	  //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);//do ssl verify
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	  curl_setopt($ch, CURLOPT_POST, 1);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($authpostvals));
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	  curl_setopt($ch, CURLOPT_COOKIEJAR, '');  //could be empty, but cause problems on some hosts
	  curl_setopt($ch, CURLOPT_COOKIEFILE, '');  //could be empty, but cause problems on some hosts

	  $response = curl_exec($ch); 
	  
	  $url='https://a.cms.omniupdate.com/files/publish';//the url we'll be uploading to.
	  //$url='https://a.cms.omniupdate.com/authentication/login';
	  //$ch = curl_init(str_replace(" ","%20",$url));
	  //curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
	  //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);//do ssl verify
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	  curl_setopt($ch, CURLOPT_POST, 1);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($imgpostvals));
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  $response = curl_exec($ch);
		
    }
    //unlink($_SESSION["originalpath"]);
    
    curl_multi_close($mh);
    if(BENCHMARKING){
      $times["Sending"] = microtime(true) - $startTime;
    }
	  
  }

function loadSession(){
  global $sessionid;
  
	$con = mysqli_connect("mysql.ais.mtu.edu","umcwebuser","umcpass01","umcwebgen");
  if(mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
   }
  //only load sessions that are less than a a old
  $query = "SELECT * FROM ou_imed_sessions where session_id = '$sessionid' AND DATE_ADD(timestamp, INTERVAL 24 HOUR) > NOW()";

  if ($result = mysqli_query($con, $query) or die("Error connecting to database. Contact <a href=\"mailto:cmshelp@mtu.edu\">cmshelp@mtu.edu</a> if the problem persists.")) {
    while($row = mysqli_fetch_assoc($result)) {
      if($row['user_data'] != '[]') {
      //echo "Row :<br>";
      /*print_r($row["user_data"]);
        echo "<br>USER DATA:";
          print_r(json_decode($row["user_data"],true));*/
        $user_data = json_decode($row["user_data"],true);
      }
      else{
        echo "<br>Retrieved data was empty<br>";
      }
    }

    /* free result set */
    mysqli_free_result($result);
  }
  //We don't have any data for this session in the database.
  if(isset($user_data)){
    $_SESSION = $user_data;//update our session variables to be what we get from the database
  }
  else{
    session_unset();//clear any info that we might have locally, since there isn't a shared session yet.
  }

  mysqli_close($con);	
  
}

function saveSession(){
  global $sessionid, $sessionip;  
  $sessiondata = json_encode($_SESSION);
	
	if(isset($sessiondata)) {
		//echo 'save session';
		$con = mysqli_connect("mysql.ais.mtu.edu","umcwebuser","umcpass01","umcwebgen");
    if(mysqli_connect_errno()) {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    //some cleanup not related to actually saving.
		if($result = mysqli_query($con, "SELECT * FROM ou_imed_sessions where DATE_ADD(timestamp, INTERVAL 24 HOUR) < NOW()")){
      //go through all inactive sessions.
      while($row = mysqli_fetch_assoc($result)){
        $data = json_decode($row["user_data"],true);
        //does the associated image still exist? delete it, so we don't have images buliding up.
        if(isset($data["originalpath"]) && file_exists($data["originalpath"])){
              //unlink($data["originalpath"]);
        }
      }
      mysqli_free_result($result);
      
      //delete the session if it is a day old, there's no reason to keep it.
      mysqli_query($con, "DELETE FROM ou_imed_sessions where DATE_ADD(timestamp, INTERVAL 24 HOUR) < NOW()");      
    }
    
    //update session to have current values (The actual saving).
    mysqli_query($con,"REPLACE INTO ou_imed_sessions (session_id,ip_address,user_data, timestamp) VALUES ('$sessionid','$sessionip','$sessiondata', NOW())");
    
		mysqli_close($con);
	}
}

function clearSession(){
  global $sessionid;
  session_regenerate_id(true);
  $con = mysqli_connect("mysql.ais.mtu.edu","umcwebuser","umcpass01","umcwebgen");
  if(mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
	mysqli_query($con, "DELETE FROM ou_imed_sessions where session_id = $sessionid");
	mysqli_close($con);
}
function printSessions(){
    $con = mysqli_connect("mysql.ais.mtu.edu","umcwebuser","umcpass01","umcwebgen");
    if (!$con) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit;
    }
      // sending query
    $result = mysqli_query($con, "SELECT * FROM ou_imed_sessions LIMIT");
    if (!$result) {
        echo "Failed to query: " . mysqli_connect_error();
        exit;
    }
    
    $fields_num = mysqli_num_fields($result);
    
    echo "<h1>Sessions</h1>";
    echo "<table border='1'><tr>";
    // printing table headers
    for($i=0; $i<$fields_num; $i++)
    {
        $field = mysqli_fetch_field($result);
        echo "<td>{$field->name} (length: {$field->length})</td>";
    }
    echo "</tr>\n";
    // printing table rows
    while($row = mysqli_fetch_row($result))
    {
        echo "<tr>";
        foreach($row as $cell)
            echo "<td>$cell</td>";
        echo "</tr>\n";
    }
    echo "</table>";
    echo ". . .";
    mysqli_free_result($result);
  
		mysqli_close($con);
}
function save_image_info($images) {
   global $defaultDimensions;
  $con = mysqli_connect("mysql.ais.mtu.edu","umcwebuser","umcpass01","umcwebgen");
  $code = "";
 foreach($images as $index=>$image){
    
      if($index !=="original"){
      $code = $_SESSION["sizes"][$index];
        $name = pathinfo($_SESSION["filename"],PATHINFO_FILENAME)."-".$defaultDimensions[$_SESSION["sizes"][$index]]["suffix"]. ".jpg";
      }else{
        $name = pathinfo($_SESSION["filename"],PATHINFO_FILENAME)."-orig.jpg";
      }
  $selection = $_SESSION["selection"][$index];
       $x1 = $selection["x1"];
       $x2 = $selection["x2"];
       $y1 = $selection["y1"];
       $y2 = $selection["y2"];
       $ou_img_path = dirname($_SESSION["webdav_folder"])."/images/".$name;

//if it is already in the databse delete it so a new entry can be added
      $query  = "DELETE FROM ou_imed_crops WHERE path = \"$ou_img_path\" AND img_code = \"$code\"";
      $return = array();
      $return   = mysqli_query($con,$query);
  //insert into the databse 
       $query  = "INSERT INTO ou_imed_crops (path,img_code,x1,y1,x2,y2) VALUES (\"$ou_img_path\",\"$code\",$x1,$y1,$x2,$y2)";
      $result = array();
      $result   = mysqli_query($con,$query);

    }
  /*
    
  $return = array();
  $result   = mysqli_query($con,$return);
*/
}

?>
