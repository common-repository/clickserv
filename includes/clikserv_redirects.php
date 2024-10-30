<?php
	// A class to handle various page redirects
	class ClickservRedirects{
		const OPTIONNAME = "ClickServ_UserID";
		static function adminPage(){
			$userId = get_option(self::OPTIONNAME);			
			function Sync($url){	
				$userId = get_option(self::OPTIONNAME);		
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"https://clickserv.v3.si/api/CoastGuard/" . $url .'?userId='. $userId);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				// $headers = array('Accept: text/plain','Content-Type: text/html; charset=utf-8;');
				$headers = array();
				$headers[] = 'Accept: text/plain';
				curl_setopt($ch, CURLOPT_HEADER, $headers);
				curl_setopt($ch, CURLOPT_NOBODY, TRUE);
				$result = curl_exec($ch);
				if (curl_errno($ch)) {
					echo 'Something went wrong, please try again later';
				}
				curl_close($ch);
			}
			?>
			<div class="clickserv-panel">
				<h3> Welcome to the ClikServ admin page!</h3>
				<form method="post">
				<label for="clickserv-userid">ClickServ account ID: </label>
				 <input id="clickserv-userid" name="clickserv-userid" type="text" placehodler="Your clickserv user ID." value="<?php echo $userId ? $userId : "" ?>"/>
				 <button class="clickserv-submit" id="submitID" name="submitID" type="submit">Submit</button>
				</form>
			</div>
			<?php
			if(isset($_POST['submitID'])){
				$input = $_POST['clickserv-userid'];
				if(!$userId){
					add_option(self::OPTIONNAME, $input, "");
				} else {
					update_option( self::OPTIONNAME,  $input, "");
				}
			}
			if(isset($_POST['SyncToSage'])){
				Sync("SyncToSage");
			}
			if(isset($_POST['SyncToWooCommerce'])){
				Sync("SyncToWooCommerce");				
			}
			if(isset($_POST['FullSync'])){
				Sync("FullSync");
			}			

			if($userId){
				?>
				<div class="clickserv-panel">
					<p>Fire various sync operations by clicking the corresponding button.</p>
					<div class='clickserv-row'>
						<form method="post">
						<button class="clickserv-btn" id="SyncToSage" name="SyncToSage" type="submit">Sync to Sage</button>
						</form>
						<form method="post">
						<button class="clickserv-btn" id="SyncToWooCommerce" name="SyncToWooCommerce" type="submit">Sync to WooCommerce</button>
						</form>
						<form method="post">
						<button class="clickserv-btn" id="FullSync" name="FullSync" type="submit">Full Sync</button>
						</form>
					</div>
				</div>
				<?php
			}			
		}
		
		static function registerPage(){
			$registerURL = "https://clickserv.co.za/sign-up.html?storeURL=" . $_SERVER['SERVER_NAME'];
			$loginURL = "https://clickserv.co.za/sign-up.html?storeURL=" . $_SERVER['SERVER_NAME'];
			?>
				<h2>Welcome to ClikServ!</h3>
				<h3>First things first. You need an account.</h3>
				<div><h4 style="display: inline">No account yet? Register <a href="<?php echo $registerURL ?>" id="registerHere">here</a></h4> | 
				<h4 style="display: inline">Already have an account? Login <a href="<?php echo  $loginURL ?>" id="loginHere">here</a></h4>
			</div>
			<?php
		}
	}
	?>