<?php 
$config = require("protected/config/app.php");
?>
<!doctype html>
<html class="no-js" ng-app="miningApp">
<head>
<meta charset="utf-8">
<title><?php echo $config['applicationName']?></title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width">
<link rel="shortcut icon" href="/favicon.ico">
<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
<!-- build:css(.) styles/vendor.css -->
<!-- bower:css -->
<link rel="stylesheet" href="./styles/bootstrap/bootstrap.css" />
<!-- endbower -->
<!-- endbuild -->
<!-- build:css(.tmp) styles/main.css -->
<link rel="stylesheet" href="styles/main.css">
<!-- endbuild -->
<style>
.headrow td {
	font-weight: bold;
	font-size: 16px;
}

tr.head td {
	background: #ddd
}
.highlight {
	font-weight: bold;
	color: #ff0000;
	font-size: 14px;
}
.error {
	color: red;
}
.done {
    color: #0000ff;
    font-weight: bold;
}
</style>
</head>
<body>
	<!--[if lt IE 10]>
      <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->


	<div class="container">
		<div class="header">
			<ul class="nav nav-pills pull-right">
				<li class="active"><a href="#">Home</a></li>
				<li><a href="#">About</a></li>
				<li><a href="#">Contact</a></li>
			</ul>
			<h3 class="text-muted">Mining script</h3>
		</div>
		
		<div ui-view="tabContent"></div>
		
		<div class="footer">
			<p>
				<a href='https://www.linkedin.com/profile/view?id=183138464&trk=nav_responsive_tab_profile'>Binh Quan</a>
			</p>
		</div>
	</div>
	
	<!-- build:js(.) scripts/vendor.js -->
	<!-- bower:js -->
	<script src="./scripts/jquery/jquery.js"></script>
	<!-- endbower -->
	<!-- endbuild -->

	<!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->

	<!-- build:js(.) scripts/plugins.js -->
	<script src="./scripts/bootstrap/affix.js"></script>
	<script src="./scripts/bootstrap/alert.js"></script>
	<script src="./scripts/bootstrap/dropdown.js"></script>
	<script src="./scripts/bootstrap/tooltip.js"></script>
	<script src="./scripts/bootstrap/modal.js"></script>
	<script src="./scripts/bootstrap/transition.js"></script>
	<script src="./scripts/bootstrap/button.js"></script>
	<script src="./scripts/bootstrap/popover.js"></script>
	<script src="./scripts/bootstrap/carousel.js"></script>
	<script src="./scripts/bootstrap/scrollspy.js"></script>
	<script src="./scripts/bootstrap/collapse.js"></script>
	<script src="./scripts/bootstrap/tab.js"></script>
	<!-- endbuild -->
	
	<script language='javascript' src='./scripts/angular/angular.js'></script>
	<script language='javascript' src='./scripts/angular/angular-route.js'></script>
	<script language='javascript' src='./scripts/angular/angular-ui-router.min.js'></script>
	<script language='javascript' src='./scripts/angular/ui-bootstrap-tpls-0.12.0.min.js'></script>
	<script language='javascript' src='./scripts/angular/lodash.min.js'></script>
	
	<script language='javascript' src='./scripts/controllers/app.js'></script>
	<script language='javascript' src='./scripts/directives/log.js'></script>
	<script language='javascript' src='./scripts/controllers/import-controllers.js'></script>
	<script language='javascript' src='./scripts/services/mining.js'></script>

	<!-- build:js({app,.tmp}) scripts/main.js -->
	<script src="./scripts/main.js"></script>
	<!-- endbuild -->
	
	<script>
	
	</script>
</body>
</html>
