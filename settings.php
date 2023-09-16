<?php
// page variables
$path_ = "../";
$encryptedPage_ = true;
$pageName = "settings";
$pageTitle = "تنظیمات";
$metaKeywords = "";

// include other files
require_once $path_.'lib/conf.php';
require_once $path_.'lib/settingArrays.php';
require_once $path_.'lib/DB.php';
require_once $path_.'lib/Validator.php';
require_once $path_.'lib/publicMethods.php';
require_once $path_.'lib/user.php';

$confObj = Conf::getInstance();
$dbObj = DB::getInstance();
$validatorObj = Validator::getInstance();
$methodObj = Methods::getInstance();
$userObj = User::getInstance();

// check and renew session
$userObj->checkAndRenewSessionId($encryptedPage_);
$UID = $userObj->getUID();

// check user professional account
$userIsProfessional = $methodObj->checkUserIsProfessional($UID);

// form msg
$formMsg = "";

// codes **************

$haveTeam = $userObj->getTID();
if(!$haveTeam){
	header("location: ".$baseDir."member/index.php");
}

// get team id
$teamId = $haveTeam;

// get team type
$teamType = $dbObj->getOne("select type from team where team_id = $teamId ");

// get user cash
$userCash = $methodObj->getUserCash($UID);

// check the submitted variable
if(isset($_POST['submitted'])){
	$submitted = $validatorObj->escapeQuery($_POST['submitted']);
}else{
	$submitted = 0;
}

// check the action variable
$aAction = array('league','playoffcup','friendlymatch','editConfig');
$editInfoSubmit = 0;

if(isset($_POST['action'])){
	$action = $validatorObj->escapeQuery($_POST['action']);
	if(!in_array($action,$aAction)){
		$action = '';
	}
}else{
	$action = '';
}

// decleare arrays & variables
$aFormData = array();
$aError = array();
$aValidate = array();
$formMsg = "";

// check for submitted
if($submitted == 1 && $action == 'league' && $teamType == 1 && false){
	// validate form data
	if(isset($_POST['league'])){
		// escape form data
		$league = $validatorObj->escapeQuery($_POST['league']);

		$validateFlag = true;
		if(!in_array($league,array(0,1))){
			$validateFlag = false;
		}

		// if validate flag is true, set the flag
		if($validateFlag){
			// set flag
			$dbObj->query("update team set league_enable = '".$league."' where team_id = '".$teamId."'");
			$formMsg = $validatorObj->raiseSuccess('وضعیت به روز شد');
		}else{
			$formMsg = $validatorObj->raiseError('اطلاعات نادرست است');
		}
	}else{
		$formMsg = $validatorObj->raiseError('اطلاعات نادرست است');
	}
}

// check for submitted
if($submitted == 1 && $action == 'playoffcup' && $teamType == 1){
	// validate form data
	if(isset($_POST['playoffcup'])){
		// escape form data
		$playoffcup = $validatorObj->escapeQuery($_POST['playoffcup']);

		$validateFlag = true;
		if(!in_array($playoffcup,array(0,1))){
			$validateFlag = false;
		}

		// if validate flag is true, set the flag
		if($validateFlag){
			// set flag
			$dbObj->query("update team set playoffcup_enable = '".$playoffcup."' where team_id = '".$teamId."'");
			$formMsg = $validatorObj->raiseSuccess('وضعیت به روز شد');
		}else{
			$formMsg = $validatorObj->raiseError('اطلاعات نادرست است');
		}
	}else{
		$formMsg = $validatorObj->raiseError('اطلاعات نادرست است');
	}
}

// check for submitted
if($submitted == 1 && $action == 'friendlymatch'){
	// validate form data
	if(isset($_POST['friendlymatch'])){
		// escape form data
		$friendlymatch = $validatorObj->escapeQuery($_POST['friendlymatch']);

		$validateFlag = true;
		if(!in_array($friendlymatch,array(0,1))){
			$validateFlag = false;
		}

		// if validate flag is true, set the flag
		if($validateFlag){
			// set flag
			$dbObj->query("update team set friendlymatch_enable = '".$friendlymatch."' where team_id = '".$teamId."'");
			$formMsg = $validatorObj->raiseSuccess('وضعیت به روز شد');
		}else{
			$formMsg = $validatorObj->raiseError('اطلاعات نادرست است');
		}
	}else{
		$formMsg = $validatorObj->raiseError('اطلاعات نادرست است');
	}
}

// check for submitted
if($submitted == 1 && $action == 'editConfig' && $teamType == 1){
	// validate form data
	if(isset($_POST['smsConf']) && isset($_POST['emailConf']) && is_array($_POST['smsConf']) && is_array($_POST['emailConf'])){
		// escape form data
		$smsConf = $validatorObj->escapeArray($_POST['smsConf']);
		$emailConf = $validatorObj->escapeArray($_POST['emailConf']);

		// set config
		$methodObj->setUserSMSConf($smsConf,$UID);
		$methodObj->setUserEmailConf($emailConf,$UID);
		$formMsg = $validatorObj->raiseSuccess('تنظیمات به روز شد');

	}else{
		$formMsg = $validatorObj->raiseError('اطلاعات نادرست است');
	}
}

// get user data
$aUserData = $dbObj->getAll("select league_enable, playoffcup_enable, friendlymatch_enable from team where team_id = '".$teamId."'");

// only main team
if($teamType == 1){
	// get user sms & email conf data
	$aUserSMSConf = $dbObj->getAll("select * from user_sms_conf where user_id = '".$UID."'");
	$aUserEmailConf = $dbObj->getAll("select * from user_email_conf where user_id = '".$UID."'");
}

// ********************

// doctype and meta tags
require_once $path_.'lib/doctype.php';
?>
<?php
// header file
require_once $path_.'lib/header.php';
?>
<div id="main-content-section">
<?php
// menu file
require_once $path_.'lib/menu.php';
?>
	<section id="title-section">
		<h1 id="title-text">
			<span id="user-cash"style="float: left;font-size: 14px;margin-top: 8px;margin-left: 50px;">سرمایه: <span id="user-cash-amount"><?php echo number_format($userCash); ?></span> <img id="coin-icon" src="<?php echo $path_ ?>media/img/gold-coin.png"/></span>
			تنظیمات
		</h1>
		<div class="clear"></div>
	</section>
	<?php echo $formMsg; ?>
	<?php if($teamType == 1){ ?>
	<?php if(false){ ?>
	<!--
	<section class="section">
		<h1 class="section-header">شرکت در لیگ</h1>
		<div class="section-body">
			<p class="marginless">در صورت تمایل می توانید از شرکت در لیگ کناره گیری نمایید.</p>
			<div class="formDiv">
				<form method="post" action="">
					<div class="formRow">
						<div class="formRow">
							<input type="radio" name="league" class="league valignMiddle" id="league_1" value="1" <?php if($aUserData[0]['league_enable'] == 1){echo 'checked="checked"';}?> /><label for="league_1">فعال</label>
							&nbsp;&nbsp;
							<input type="radio" name="league" class="league valignMiddle" id="league_2" value="0" <?php if($aUserData[0]['league_enable'] == 0){echo 'checked="checked"';}?> /><label for="league_2">غیرفعال</label>
						</div>
					</div>
					<div class="formRow">
						<div class="alignCenter">
							<input type="submit" name="submit" id="submit" value="ذخیره" class="buttonOrange" />
							<input type="hidden" name="action" id="action" value="league" />
							<input type="hidden" name="submitted" id="submitted" value="1" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</section>
	<div class="sectionSeprator"></div>
	-->
	<?php } ?>
	<section class="section">
		<h1 class="section-header">شرکت در جام حذفی</h1>
		<div class="section-body">
			<p class="marginless">در صورت تمایل می توانید از شرکت در جام حذفی کناره گیری نمایید.</p>
			<div class="formDiv">
				<form method="post" action="">
					<div class="formRow">
						<div class="formRow">
							<input type="radio" name="playoffcup" class="playoffcup valignMiddle" id="playoffcup_1" value="1" <?php if($aUserData[0]['playoffcup_enable'] == 1){echo 'checked="checked"';}?> /><label for="playoffcup_1">فعال</label>
							&nbsp;&nbsp;
							<input type="radio" name="playoffcup" class="playoffcup valignMiddle" id="playoffcup_2" value="0" <?php if($aUserData[0]['playoffcup_enable'] == 0){echo 'checked="checked"';}?> /><label for="playoffcup_2">غیرفعال</label>
						</div>
					</div>
					<div class="formRow">
						<div class="alignCenter">
							<input type="submit" name="submit" id="submit" value="ذخیره" class="buttonOrange" />
							<input type="hidden" name="action" id="action" value="playoffcup" />
							<input type="hidden" name="submitted" id="submitted" value="1" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</section>
	<div class="sectionSeprator"></div>
	<?php } ?>
	<section class="section">
		<h1 class="section-header">دریافت درخواست بازی دوستانه</h1>
		<div class="section-body">
			<p class="marginless">در صورت تمایل می توانید قابلیت دریافت درخواست بازی دوستانه را غیرفعال نمایید.</p>
			<div class="formDiv">
				<form method="post" action="">
					<div class="formRow">
						<div class="formRow">
							<input type="radio" name="friendlymatch" class="friendlymatch valignMiddle" id="friendlymatch_1" value="1" <?php if($aUserData[0]['friendlymatch_enable'] == 1){echo 'checked="checked"';}?> /><label for="friendlymatch_1">فعال</label>
							&nbsp;&nbsp;
							<input type="radio" name="friendlymatch" class="friendlymatch valignMiddle" id="friendlymatch_2" value="0" <?php if($aUserData[0]['friendlymatch_enable'] == 0){echo 'checked="checked"';}?> /><label for="friendlymatch_2">غیرفعال</label>
						</div>
					</div>
					<div class="formRow">
						<div class="alignCenter">
							<input type="submit" name="submit" id="submit" value="ذخیره" class="buttonOrange" />
							<input type="hidden" name="action" id="action" value="friendlymatch" />
							<input type="hidden" name="submitted" id="submitted" value="1" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</section>
	<?php if($teamType == 1){ ?>
	<div class="sectionSeprator"></div>
	<section class="section">
		<h1 class="section-header">تنظیمات ارسال آگاه سازها</h1>
		<div class="section-body">
			<p class="marginless">در این قسمت می توانید مشخص کنید که چه زمانی سیستم شما را از رویدادهای بازی آگاه کند.</p>
			<div class="formDiv">
				<form method="post" action=""><br />
					<div class="listDiv">
						<table class="listTable" cellspacing="0" cellpadding="0">
							<thead>
								<tr>
									<th>آگاه ساز بازی</th>
									<th>آگاه ساز نتیجه بازی</th>
									<th>آگاه ساز آغاز لیگ</th>
									<th>آگاه ساز درخواست بازی دوستانه</th>
									<th>آگاه ساز پیام خصوصی</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<div>
											<input type="checkbox" name="emailConf[]" class="emailConf valignMiddle" id="emailConf_match" value="match" <?php if($aUserEmailConf[0]['match']){echo 'checked="checked"';}?> />
											<label for="emailConf_match">ارسال ایمیل</label>
										</div>
										<?php if($userIsProfessional){ ?>
										<div>
											<input type="checkbox" name="smsConf[]" class="smsConf valignMiddle" id="smsConf_match" value="match" <?php if($aUserSMSConf[0]['match']){echo 'checked="checked"';}?> />
											<label for="smsConf_match">ارسال پیامک</label>
										</div>
										<?php } ?>
									</td>
									<td>
										<div>
											<input type="checkbox" name="emailConf[]" class="emailConf valignMiddle" id="emailConf_result" value="result" <?php if($aUserEmailConf[0]['result']){echo 'checked="checked"';}?> />
											<label for="emailConf_result">ارسال ایمیل</label>
										</div>
										<?php if($userIsProfessional){ ?>
										<div>
											<input type="checkbox" name="smsConf[]" class="smsConf valignMiddle" id="smsConf_result" value="result" <?php if($aUserSMSConf[0]['result']){echo 'checked="checked"';}?> />
											<label for="smsConf_result">ارسال پیامک</label>
										</div>
										<?php } ?>
									</td>
									<td>
										<div>
											<input type="checkbox" name="emailConf[]" class="emailConf valignMiddle" id="emailConf_league" value="league" <?php if($aUserEmailConf[0]['league']){echo 'checked="checked"';}?> />
											<label for="emailConf_league">ارسال ایمیل</label>
										</div>
										<?php if($userIsProfessional){ ?>
										<div>
											<input type="checkbox" name="smsConf[]" class="smsConf valignMiddle" id="smsConf_league" value="league" <?php if($aUserSMSConf[0]['league']){echo 'checked="checked"';}?> />
											<label for="smsConf_league">ارسال پیامک</label>
										</div>
										<?php } ?>
									</td>
									<td>
										<div>
											<input type="checkbox" name="emailConf[]" class="emailConf valignMiddle" id="emailConf_friendly" value="friendly" <?php if($aUserEmailConf[0]['friendly']){echo 'checked="checked"';}?> />
											<label for="emailConf_friendly">ارسال ایمیل</label>
										</div>
										<?php if($userIsProfessional){ ?>
										<div>
											<input type="checkbox" name="smsConf[]" class="smsConf valignMiddle" id="smsConf_friendly" value="friendly" <?php if($aUserSMSConf[0]['friendly']){echo 'checked="checked"';}?> />
											<label for="smsConf_friendly">ارسال پیامک</label>
										</div>
										<?php } ?>
									</td>
									<td>
										<div>
											<input type="checkbox" name="emailConf[]" class="emailConf valignMiddle" id="emailConf_message" value="message" <?php if($aUserEmailConf[0]['message']){echo 'checked="checked"';}?> />
											<label for="emailConf_message">ارسال ایمیل</label>
										</div>
										<?php if($userIsProfessional){ ?>
										<div>
											<input type="checkbox" name="smsConf[]" class="smsConf valignMiddle" id="smsConf_message" value="message" <?php if($aUserSMSConf[0]['message']){echo 'checked="checked"';}?> />
											<label for="smsConf_message">ارسال پیامک</label>
										</div>
										<?php } ?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="formRow">
						<div class="alignCenter marginTop10">
							<input type="submit" name="submit" id="submit" value="ذخیره" class="buttonOrange" />
							<input type="hidden" name="action" id="action" value="editConfig" />
							<input type="hidden" name="submitted" id="submitted" value="1" />
							<input type="hidden" name="emailConf[]" value="1" />
							<input type="hidden" name="smsConf[]" value="1" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</section>
	<?php } ?>
</div>
<?php
// footer file
require_once $path_.'lib/footer.php';
?>
