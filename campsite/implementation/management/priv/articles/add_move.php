<?php 
require_once($_SERVER['DOCUMENT_ROOT']. "/$ADMIN_DIR/articles/article_common.php");
require_once($_SERVER['DOCUMENT_ROOT']. "/classes/ArticleType.php");

global $Campsite;

list($access, $User) = check_basic_access($_REQUEST);
if (!$access) {
	header("Location: /$ADMIN/logout.php");
	exit;
}
if (!$User->hasPermission('AddArticle')) {
	camp_html_display_error(getGS("You do not have the right to add articles."));
	exit;
}

// The article location dropdowns cause this page to reload,
// so we need to preserve the state with each refresh.
$f_article_name = Input::Get('f_article_name', 'string', '', true);
$f_article_type = Input::Get('f_article_type', 'string', '', true);
$f_article_language = Input::Get('f_article_language', 'int', 0, true);

// For choosing the article location.
$f_destination_publication_id = Input::Get('f_destination_publication_id', 'int', 0, true);
$f_destination_issue_number = Input::Get('f_destination_issue_number', 'int', 0, true);
$f_destination_section_number = Input::Get('f_destination_section_number', 'int', 0, true);

if ($f_article_language <= 0) {
	$f_destination_publication_id = 0;
	$f_destination_issue_number = 0;
	$f_destination_section_number = 0;
}

if (!Input::IsValid()) {
	camp_html_display_error(getGS('Invalid input: $1', Input::GetErrorString()), $_SERVER['REQUEST_URI']);
	exit;	
}

$allIssues = array();
if ($f_destination_publication_id > 0) {
	$allIssues = Issue::GetIssues($f_destination_publication_id, 
								  $f_article_language, null, null, 
								  array("LIMIT" => 50, "ORDER BY" => array("Number" => "DESC")));
}

$allSections = array();
if ($f_destination_issue_number > 0) {
	$selectedIssue =& new Issue($f_destination_publication_id, $f_article_language, $f_destination_issue_number);
	$allSections = 	$allSections = Section::GetSections($f_destination_publication_id, $f_destination_issue_number, $f_article_language, array("ORDER BY" => array("Name" => "ASC")));
}

$allArticleTypes = ArticleType::GetArticleTypes();
$allLanguages = Language::GetLanguages();

// added by sebastian
if (function_exists ("incModFile")) {
	incModFile ();
}

$crumbs = array();
$crumbs[] = array(getGS("Actions"), "");
$crumbs[] = array(getGS("Add new article"), "");
echo camp_html_breadcrumbs($crumbs);

?>

<?php
if (sizeof($allArticleTypes) == 0) {
?>
<p>
<table border="0" cellspacing="0" cellpadding="6" align="center" class="table_input">
<tr>
	<td align="center">
	<font color="red">
	<?php putGS("No article types were defined. You must create an article type first."); ?>
	</font>
	<p><b><a href="/<?php echo $ADMIN; ?>/article_types/"><?php putGS("Edit article types"); ?></a></b></p>
	</td>
</tr>
</table>
</p>
<?php
} else {
?>

<script type="text/javascript" src="<?php echo $Campsite['WEBSITE_URL']; ?>/javascript/fValidate/fValidate.config.js"></script>
<script type="text/javascript" src="<?php echo $Campsite['WEBSITE_URL']; ?>/javascript/fValidate/fValidate.core.js"></script>
<script type="text/javascript" src="<?php echo $Campsite['WEBSITE_URL']; ?>/javascript/fValidate/fValidate.lang-enUS.js"></script>
<script type="text/javascript" src="<?php echo $Campsite['WEBSITE_URL']; ?>/javascript/fValidate/fValidate.validators.js"></script>	

<P>
<FORM NAME="add_article" METHOD="GET" ACTION="add_move.php" onsubmit="return validateForm(this, 0, 1, 0, 1, 8);">
<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="6" class="table_input">
<TR>
	<TD COLSPAN="2">
		<B><?php  putGS("Add new article"); ?></B>
		<HR NOSHADE SIZE="1" COLOR="BLACK">
	</TD>
</TR>
<TR>
	<td valign="top">
		<table>
		<tr>
			<TD ALIGN="RIGHT" ><?php  putGS("Name"); ?>:</TD>
			<TD>
			<INPUT TYPE="TEXT" NAME="f_article_name" SIZE="40" MAXLENGTH="255" class="input_text" alt="blank" emsg="<?php putGS('You must complete the $1 field.', getGS('Name')); ?>" value="<?php echo htmlspecialchars($f_article_name); ?>">
			</TD>
		</TR>
		<TR>
			<TD ALIGN="RIGHT" ><?php  putGS("Type"); ?>:</TD>
			<TD>
				<SELECT NAME="f_article_type" class="input_select" alt="select" emsg="<?php putGS('You must complete the $1 field.', getGS('Article Type')); ?>">
				<option></option>
				<?php 
				foreach ($allArticleTypes as $tmpType) {
					camp_html_select_option($tmpType, $f_article_type, $tmpType);
				}
				?>
				</SELECT>
			</TD>
		</TR>
		<TR>
			<TD ALIGN="RIGHT" ><?php  putGS("Language"); ?>:</TD>
			<TD>
				<script>
				function on_language_select(p_select)
				{
					<?php 
					// Only submit the form in the case where the user changes the 
					// language after they have chosen a publication, in which case
					// we have to reload the issues.
					if ($f_article_language != 0) { ?>
					p_select.form.submit();
					<?php } else { ?>
					if (p_select.selectedIndex == 0) {
						p_select.form.f_destination_publication_id.disabled = true; 						
					} else {
						p_select.form.f_destination_publication_id.disabled = false; 
					}
					<?php } ?>
				}
				</script>
				<SELECT NAME="f_article_language" alt="select" emsg="<?php putGS("You must select a language.")?>" class="input_select" onchange="on_language_select(this);">
				<option value="0"><?php putGS("---Select language---"); ?></option>
				<?php 
			 	foreach ($allLanguages as $tmpLanguage) {
			 		camp_html_select_option($tmpLanguage->getLanguageId(), 
			 								$f_article_language, 
			 								$tmpLanguage->getNativeName());
		        }
				?>			
				</SELECT>
			</TD>
		</TR>
		</table>
	</td>
	
	<?php if ($User->hasPermission("MoveArticle")) { ?>
	<td style="border-left: 1px solid black;">
		<TABLE>
		<TR>
			<td colspan="2" style="padding-left: 20px; padding-bottom: 5px;font-size: 10pt; font-weight: bold;"><?php  putGS("Select location (optional):"); ?></TD>
		</TR>
		<TR>
			<TD VALIGN="middle" ALIGN="RIGHT" style="padding-left: 20px;"><?php  putGS('Publication'); ?>: </TD>
			<TD valign="middle" ALIGN="LEFT">
				<?php if ( count($Campsite["publications"]) > 0) { ?>
				<SELECT NAME="f_destination_publication_id" class="input_select" ONCHANGE="if (this.options[this.selectedIndex].value != <?php p($f_destination_publication_id); ?>) {this.form.submit();}" <?php if ($f_article_language == 0) { echo "disabled"; } ?>>
				<OPTION VALUE="0"><?php  putGS('---Select publication---'); ?></option>
				<?php 
				foreach ($Campsite["publications"] as $tmpPublication) {
					camp_html_select_option($tmpPublication->getPublicationId(), $f_destination_publication_id, $tmpPublication->getName());
				}
				?>
				</SELECT>
				<?php
				}
				else {
					?>
					<SELECT class="input_select" DISABLED><OPTION><?php  putGS('No publications'); ?></option></SELECT>
					<?php
				}
				?>
			</td>
		</tr>
		
		<tr>
			<TD VALIGN="middle" ALIGN="RIGHT" style="padding-left: 20px;"><?php  putGS('Issue'); ?>: </TD>
			<TD valign="middle" ALIGN="LEFT">
				<?php 
				if (($f_destination_publication_id > 0) && (count($allIssues) > 0)) {
					?>
					<SELECT NAME="f_destination_issue_number" class="input_select" ONCHANGE="if (this.options[this.selectedIndex].value != <?php p($f_destination_issue_number); ?>) { this.form.submit(); }">
					<OPTION VALUE="0"><?php  putGS('---Select issue---'); ?></option>
					<?php 
					foreach ($allIssues as $tmpIssue) {
						camp_html_select_option($tmpIssue->getIssueNumber(), $f_destination_issue_number, $tmpIssue->getName());
					}
					?>
					</SELECT>
					<?php  
				} 
				else { 
					?><SELECT class="input_select" DISABLED><OPTION><?php  putGS('No issues'); ?></SELECT>
					<?php  
				} 
				?>
			</td>
		</tr>
		
		<tr>	
			<TD VALIGN="middle" ALIGN="RIGHT" style="padding-left: 20px;"><?php  putGS('Section'); ?>: </TD>
			<TD valign="middle" ALIGN="LEFT">
				<?php if (($f_destination_publication_id > 0) 
						  && (count($allIssues) > 0) 
						  && ($f_destination_issue_number > 0) 
						  && (count($allSections) > 0)) { ?>
				<SELECT NAME="f_destination_section_number" class="input_select">
				<OPTION VALUE="0"><?php  putGS('---Select section---'); ?>
				<?php 
				foreach ($allSections as $tmpSection) {
					camp_html_select_option($tmpSection->getSectionNumber(), $f_destination_section_number, $tmpSection->getName());
				}
				?>
				</SELECT>
				<?php  
				} 
				else { 
					?><SELECT class="input_select" DISABLED><OPTION><?php  putGS('No sections'); ?></SELECT>
					<?php  
				}
				?>
				</TD>
		</tr>
		</TABLE>
	</td>
	<?php } ?>
</tr>
<TR>
	<TD COLSPAN="2" align="center">
		<HR NOSHADE SIZE="1" COLOR="BLACK">
		<INPUT TYPE="submit" NAME="save" VALUE="<?php  putGS('Save'); ?>" class="button" onclick="document.forms.add_article.action='do_add.php';">
	</TD>
</TR>
</TABLE>
</FORM>
<P>

<?php } ?>
<?php camp_html_copyright_notice(); ?>