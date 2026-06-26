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

  <center>
  <table border="0" cellpadding="6" cellspacing="6" width="750">
	<!--<tr>
      <td width="106" valign="top">
        <img border="0" style="margin-top: 5px;" src="images/team-aleksya-100.jpg" width="100" >
      </td>
      <td width="548" valign="top" align="left">
        <font size="4" face="verdana">Aleksya Aguirre</font> 
        <font size="3" face="verdana" color="gray">
        - Workshop Leader / Developer
          <br>
          MIT, BS Mech Engr 2015
          <br>
          Hobbies: Singing, drawing, and volleyball
          <br>
          Heroes: Link from The Legend of Zelda
	      <br>
		  Skills wanted: Super good dancing skills
	      <br>
          Favorite Quote: "Today YOU are YOU that is truer than true. There is no one alive who is YOUER than YOU." -Dr. Seuss
		  <br>
		  <a href="http://www.invention-universe.com/aleksya/aleksya-index.html">BIY Website</a> 
		   | 
		  <a href="http://aleksya-aguirre.squarespace.com/">Personal Website</a>
        </font>
      </td>
    </tr>-->
   
    <tr>
      <td width="106" valign="top">
		<a href="http://www.youtube.com/watch?v=PO97FA73exE">
	       <img border="0" style="margin-top: 5px;" src="images/team-will-chou.jpg" width="100" height="100">
		</a>
      </td>
      <td width="548" valign="top" align="left">
        <font size="4" face="verdana">Will Chou</font>  <font size="3" face="verdana" color="gray">
        - Platform Developer
        <br>
        Cornell, BS, MS, Computer Science, 2009 
        <br>
        Hobbies: breakdancing, volleyball, and computer games
        <br>
        Heroes: My dad
	<br>
        Favorite Quote: &quot;The time you enjoy wasting is not wasted time.&quot; - Bertrand Russell
	<br>
        <a href="http://www.williamchou.com/" target="_blank">Resume</a>
        ,
        <a href="http://www.youtube.com/watch?v=PO97FA73exE" target="_blank">Bboying Video</a>
        </font>
      </td>
    </tr>

    <tr>
      <td width="106" valign="top">
		<img border="0" style="margin-top: 5px;" src="images/team-andy-gauthier-100.jpg" width="100" height="100">
      </td>
      <td width="548" valign="top" align="left">
        <font size="4" face="verdana">Andy Gauthier</font>  <font size="3" face="verdana" color="gray">
        - Project Manager
        <br>
        Cornell, BS, MS Mech Engr, 2013
        <br>
        Hobbies: Tennis, Ultimate Frisbee, Formula 1 
        <br>
        Heroes: Pinky and the brain
        <br>
		Fav Distraction: <a href="http://whatif.xkcd.com/">What if?</a>
		<br>
        Fav Quote: "Almost only counts in horseshoes, darts and hand grenades" 
        </font>
      </td>
    </tr>

    <tr>
      <td width="106" valign="top">
		<img border="0" style="margin-top: 5px;" src="images/team-adia-wallace-100.jpg" width="100" height="100">
      </td>
      <td width="548" valign="top" align="left">
        <font size="4" face="verdana">Adia Wallace</font>  <font size="3" face="verdana" color="gray">
        - Content Developer and Workshop Leader
        <br>
        Harvard GSE, MEd, 2015
        <br>
        Hobbies: Reading, Making 3D things, Online window shopping 
        <br>
        Heroes: Mae Jemison, Michael Jordan in "Space Jam," Hayao Miyazaki
        <br>
        Fav Quote: "Want to change the world?  
		There's nothing to it."  
		- Willy Wonka and the Chocolate Factory, "Pure Imagination" 
        </font>
      </td>
    </tr>
	
	<tr>
      <td width="106" valign="top">
        <img border="0" style="margin-top: 5px;" src="images/team-aditi-dugar-100.jpg" width="100" height="100">
      </td>
      <td width="548" valign="top" align="left">
        <font size="4" face="verdana">Aditi Dugar</font>&nbsp;<font size="3" face="verdana" color="gray">
        - Workshop Leader / Developer
	 <br> 
	 Cornell, BS Mech Engr, 2011
	 <br>
	 Hobbies: Singing, piano, running
	 <br>
	 Heroes: Sara Bareilles, Steve Jobs
	 <br>
         Favorite Quote: "A friend is someone who knows the song in your heart and can sing it back to you
	 when you have forgotten the words." - C.S. Lewis
	 <br>
      </td>
    </tr>

    <tr>
      <td width="106" valign="top">
        <img border="0" style="margin-top: 5px;" src="images/team-stephen-wong-100.jpg" width="100" height="100">
      </td>
      <td width="548" valign="top" align="left">
        <font size="4" face="verdana">Stephen Wong</font>&nbsp;<font size="3" face="verdana" color="gray">
        - Workshop Leader / Developer
        <br>
        Cornell, BS Elec Engr, 2011 
        <br>
        Hobbies: Building things (working on Parkour'ing) 
        <br>
        Heroes: Dad, Steve Jobs, Oda Eiichiro
	    <br>
        <a href="resume-1-16-14/resume-stephen-wong-2009.pdf">Resume</a></font>
      </td>
    </tr>

	<tr>
      <td width="106" valign="top">
        <img border="0" style="margin-top: 5px;" src="images/team-yin-100.png" width="100" >
      </td>
      <td width="548" valign="top" align="left">
        <font size="4" face="verdana">Yin Liu</font> 
        <font size="3" face="verdana" color="gray">
        - Visual Developer
          <br>
          SMFA / Tufts University, MFA Studio Art 2015
          <br>
          Hobbies: Board Games / Adventure (Exploring everything unkown)
          <br>
          Heroes: Nature (Sun, Moon, Stars, Jungle, Ocean, Wind, Snow...)  
	      <br>
		  Skills wanted: Fly / Communicate with any species
	      <br>
		  <a href="http://www.hello-inin.com">Website</a>
        </font>
      </td>
    </tr>	
	
    <tr>
      <td width="106" valign="top">
	    <img border="0" style="margin-top: 5px;" src="images/team-anna-polonyi-100.jpg" width="100" height="100">
	  </td>
      <td width="548" valign="top" align="left">
        <font size="4" face="verdana">Anna Polonyi</font> 
        <font size="3" face="verdana" color="gray">
          - Workshop Leader / Writer
	  <br>
          Harvard, BA History &amp; Literature, 2010
	  <br>
          Hobbies: Photography, making things for other people
	  <br>
	  Heroes: The Little Prince, Baron Munchausen, Shel Silverstein
	  <br>
          Least Favorite Course: Latin
	  <br>
          Skills Wanted: Flying
	  <br>
        </font>
      </td>
    </tr>

    <tr>
      <td width="106" valign="top">
        <img border="0" style="margin-top: 5px;" src="images/team-hearson1.jpg" width="100" height="100">
      </td>
      <td width="548" valign="top" align="left">
       <font size="4" face="verdana">Wenyu Zhang</font> 
		<font size="3" face="verdana" color="gray">
		  - Platform Development
         <br>
          Purdue, BS Computer Science, 2012
		  <br>
		  Hobbies: Computers, Robots, Math, Flute and Guitar
		  <br>
		  Heroes: KITT and Alan Turing
		  <br>
		  Favorite Quote: "Let's do it the engineer's way!"
		  <br>
          <a href="resume-1-16-14/resume-hearson-2009.pdf" target="_blank">Resume</a>
		   | 
		  <a href="http://about.me/wenyuzhang" target="_blank">Website</a>
		</font>
      </td>
    </tr>      
   <tr>
	 <td width="106" valign="top">
		   <img border="0" style="margin-top: 5px;" src="images/team-jamie.jpg" width="100" height="100">
	 </td>
	 <td width="548" valign="top" align="left">
	   <font size="4" face="verdana">Jamie Mckiernan</font>  <font size="3" face="verdana" color="gray">
	   - Graphic Designer
	   <br>
	   Mass Art, BFA Animation &amp; Illustration, 2009
	   <br>
	   Hobbies: Animation, gaming, watching movies from the 40's 
	   <br>
	   Heroes: Bill Watterson, Fred Astaire, Pixar Animation
	   <br>
	   Favorite Quote: &quot;A little nonsense now and then is relished by the
	   wisest men.&quot; ~ Willy Wonka<br>
	   <a href="http://jmckiernan.com/" target="_blank">Blog</a>
	   </font>
	 </td>
   </tr>
    <tr>
      <td width="106" valign="top">
        <img border="0" style="margin-top: 5px;" src="images/team-jacob-bredthauer2-100.jpg" width="100" height="100">
      </td>
      <td width="548" valign="top" align="left">
        <font size="4" face="verdana">Jacob Bredthauer</font> 
		<font size="3" face="verdana" color="gray">
		  - Platform Developer / Workshop Leader
          <br>
          MIT, BS Math &amp; Computer Science,&nbsp; 2011
          <br>
          Hobbies: MIT a capella group
          <br>
          Heroes:&nbsp;My Parents; Tom Osborne<br>
        Favorite TV shows: Lost, 24, Heroes</font>
      </td>
    </tr>

    <tr>
      <td width="106" valign="top">
	    <img border="0" style="margin-top: 5px;" src="images/team-lindley.jpg" width="100" height="100">
	  </td>
      <td width="548" valign="top" align="left">
        <font size="4" face="verdana"> 
		Lindley Graham</font> 
		<font size="3" face="verdana" color="gray">
		- Workshop Leader
        <br>
        MIT, BS Aeronautical Engineering 2010 
        <br>
        Hobbies: Reading, hanging out with friends 
        <br>
        Heroes: My parents
        </font>
      </td>
    </tr>

    <tr>
      <td width="106" valign="middle" align="center">
        <img border="0" style="margin-top: 5px;" src="images/team-xiaofei2.jpg" width="100" height="100">
      </td>
      <td width="498" valign="top" align="left">
       <font size="4" face="verdana">Xiaofei Fu</font> <font size="3" face="verdana" color="gray">-
       Content Development
        <br>
       CCMU, Beijing, BS Pharmaceutical Science, 2010<br>
       UMass, MS Chemistry, 2014 
        <br>
        Hobbies:&nbsp;Cooking and traveling 
        <br>
        Heroes:&nbsp;Taylor Swift<br>
       <a href="interns/website-xiaofei/index-xiaofei.html">
       Website</a></font>
      </td>
    </tr>
	
    <tr>
      <td width="106" valign="middle" align="center">
        <img border="0" style="margin-top: 5px;" src="images/team-richard-weiner-150.jpg" width="100" height="100">
      </td>
      <td width="498" valign="top" align="left">
       <font size="4" face="verdana">Richard Weiner</font> <font size="3" face="verdana" color="gray">-
        BIY Landlord
        <br>
        U Penn, City Planning
		<br>
        Hobbies:&nbsp; Being a hippie and repairing broken stuff.
        <br>
        Heroes:&nbsp;<br>
		Favorite Book:<br>
		Favorite Quote:
	   </font>
      </td>
    </tr>
	
  </table>
  </center>
</div>
</div>

<!------------ End Body ----------->

<?php include("../footer.php"); ?>
</body>
