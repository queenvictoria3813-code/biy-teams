<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
   <meta name="google-site-verification" content="Hs0DHxlZlSsN7u62aUUeasoZbiNcFy3Qx2vN7cQrh7U" />
   <title>
      Build-It-Yourself toys, robots, puppets, contraptions, computer programs.
   </title>

   <META Name="description" Content=
   "Build your own toys and robots.
   Build a website, computer game, digital music score, 3D graphics animation.     
   Collaborate with engineers, artists and toy inventors.">
	
   <META Name="keywords" Content=
   "LEGO, LEGO inventions, LEGO NXT, LEGO Mindstorms, LEGO robots
   MIT Media Lab, Life Long Kindergarten, Scratch, games, computer games
   toys, toy models, toy robots, toy building blocks, toy building, 
   toy inventions, toy inventors, toy trucks, toy plans
   remote control trucks, building blocks, hand made toys, 
   build it yourself toys, remote control toys, electronic toys, 
   inventions, inventors, Rube Goldberg toys, robots, puppets, 
   contraptions, kid's art, kid's crafts, toy arts and crafts, 
   art education, camp, after school activities, young engineers">
	
	<link rel="stylesheet" type="text/css" href="../css/global.css">
	<link rel="stylesheet" type="text/css" href="../css/body.css">
	
	<script type="text/javascript">

	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-16000307-1']);
	  _gaq.push(['_setDomainName', 'build-it-yourself.com']);
	  _gaq.push(['_trackPageview']);

	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();

	</script>
	
</head>

<body bgcolor="#ffffff">

<?php include("../header.php"); ?>

<!------------ Start Body -----------> 


	<div class="body-content">
<center>
  <b><font size="5" face="verdana">Crew</font></b>
  <br>
<font face="verdana" color="#808080" size="4">
	A number of engineers, artists, educators, students and business developers<br>
	have made valuable contributions to Build-It-Yourself.
</font>
<br>
</center>

<div align="center">
<?php
//current team members listed is team csv file in same directory as this file
// get the csv file - add to array
$csv = array_map('str_getcsv', file('team-page.csv'));
?>
<center>
<table border="0" cellpadding="6" cellspacing="6" width="750">
<?php
//get values
foreach ($csv as $key => $value) {  
if($value[11]=='TRUE'){?>
	<tr>
      <td width="106" valign="top">
		<img border="0" style="margin-top: 5px;" src="<?php echo $value[14]; ?>" width="100" height="100">
      </td>
      <td width="548" valign="top" align="left">
        <font size="4" face="verdana"><?php echo $value[0]; ?></font>  <font size="3" face="verdana" color="gray">
        - <?php echo $value[1]; ?>
        <br>
<!-- Education 1 Field -->
		<?php if (!empty($value[2])) {?> 
          <?php echo $value[2]; ?>
          <br>
		<?php };?>
<!-- Education 2 Field -->
        <?php if (!empty($value[3])) {?> 
          <?php echo $value[3]; ?>
          <br>
		<?php };?>
<!-- Hobbies Field -->
        <?php if (!empty($value[4])) {?> 
          Hobbies: <?php echo $value[4]; ?>
          <br>
		<?php };?>
<!-- Home Town Field -->
        <?php if (!empty($value[16])) {?> 
          Home Town: <?php echo $value[16]; ?>
          <br>
		<?php };?>
<!-- Heroes Field -->
        <?php if (!empty($value[5])) {?> 
          Heroes: <?php echo $value[5]; ?>
          <br>
		<?php };?>
<!-- Goals Field -->
        <?php if (!empty($value[17])) {?> 
          Goal: <?php echo $value[17]; ?>
          <br>
		<?php };?>
<!-- Wish Field -->
        <?php if (!empty($value[18])) {?> 
          Wish: <?php echo $value[18]; ?>
          <br>
		<?php };?>
<!-- Least Favorite Thing to Do! Field -->
        <?php if (!empty($value[6])) {?> 
          Least Favorite Thing to Do: 
		  <?php echo $value[6]; ?>
          <br>
		<?php };?>
<!-- Favorite Quote Field -->
        <?php if (!empty($value[7])) {?> 
          Favorite Quote: <?php echo $value[7]; ?>
          <br>
		<?php };?>
<!-- Skills Wanted Field -->
        <?php if (!empty($value[8])) {?> 
          Skills Wanted: <?php echo $value[8]; ?>
          <br>
		<?php };?>       
<!-- Website Fields -->
        <?php if (!empty($value[9])) {?>
          <a href="<?php echo $value[10]; ?>">
		    <?php echo $value[9];?>
          </a> 
		<?php };?>
<!-- Website 2 -->
        <?php if (!empty($value[19])) {?>
          | <a href="<?php echo $value[20]; ?>">
		    <?php echo $value[19];?>
          </a>
		<?php };?>
        </font>
      </td>
    </tr>   
<?php };}
?>
</table>
</center>

  
</div>
</div>

<!------------ End Body ----------->

<?php include("../footer.php"); ?>
</body>
