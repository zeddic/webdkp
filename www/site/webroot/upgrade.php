<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<title>WebDKP Upgrade</title>
</html>
<body>
	<style type="text/css">
		body {
			margin: 0px;
			padding: 0px;
			font-family: "Verdana";
			font-size: 11pt;
			line-height: 150%;
		}
		#notice {
			margin: 50px auto;
			width: 400px;
		}


	/* set the image to use and establish the lower-right position */
	.cssbox, .cssbox_body, .cssbox_head, .cssbox_head h2{
		background: transparent url(temp/images/welcomecorner.png) no-repeat bottom right}
	.cssbox{
	/* intended total box width - padding-right(next) */
		/*width:290px; !important;*/ /* IE Win = width - padding */
		width: 400px;
	/* the gap on the right edge of the image (not content padding) */
		padding-right:15px; /* use to position the box */
	 	margin:0px auto;

		}

	/* set the top-right image */
	.cssbox_head{background-position:top right;
	/* pull the right image over on top of border */
		margin-right:-15px;
	/* right-image-gap + right-inside padding */
		padding-right:40px}

	/* set the top-left image */
	.cssbox_head h2{
		background-position:top left;
		margin:0; /* reset main site styles*/
		border:0; /* ditto */
	/* padding-left = image gap + interior padding ... no padding-right */
		padding:15px 0px 10px 15px;
		height:auto !important;
		font-weight: normal;
		height:1%} /* IE Holly Hack */

	/* set the lower-left corner image */
	.cssbox_body{
		background-position:bottom left;
		margin-right:25px; /* interior-padding right */
		padding:2px 0 15px 15px} /* mirror .cssbox_head right/left */


	</style>
	<br />
	<br />
	<div class="cssbox">
		<div class="cssbox_head">
			<h2><img src="temp/images/peon.gif" style="vertical-align:sub"> Upgrade in Progress </h2>
		</div>
		<div class="cssbox_body">
			<p>
				WebDKP is currently down.  We are currently migrating to a dedicated server.
				<br />
				Please accept our apologizes we expect to be back up soon.
				<br />
				Thanks!
				<br />
				WebDKP

			</p>

		</div>
	</div>

</body>