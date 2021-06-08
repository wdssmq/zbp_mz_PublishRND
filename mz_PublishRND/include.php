<?php
#注册插件
RegisterPlugin("mz_PublishRND", "ActivePlugin_mz_PublishRND");

function ActivePlugin_mz_PublishRND()
{
  Add_Filter_Plugin('Filter_Plugin_Edit_Response3', 'mz_PublishRND_AddSwitch');
  Add_Filter_Plugin('Filter_Plugin_PostArticle_Core', 'mz_PublishRND_post');
  Add_Filter_Plugin('Filter_Plugin_Feed_End', 'mz_PublishRND_Run');
}
function mz_PublishRND_Run()
{
  global $zbp;
  $curDate = (int)date('Ymd');
  $odds = $zbp->Config('mz_PublishRND')->odds;
  $list = GetList(37, null, null, null, null,  null, array("post_status" => 2));
  foreach ($list as $article) {
    if (false === strpos($article->Content, "<!--PubRND-->")) {
      continue;
    }
    if (($curDate + $article->ID) % $odds == 0) {
      $article->Status = 0;
      $article->Save();
    }
  }
}
function mz_PublishRND_post(&$article)
{
  // global $zbp;
  if (GetVars('edtPubRND', 'POST') != 1) {
    return;
  }
  $article->Status = 2;
  $article->Content = str_replace("<!--PubRND-->", "", $article->Content);
  $article->Content .= "<!--PubRND-->";
}
function mz_PublishRND_AddSwitch()
{
?>
  <div id="PubRND" class="editmod">
    <label for="edtPubRND" class="editinputname">自动发布（随机）:</label>
    <input id="edtPubRND" name="edtPubRND" type="text" value="0" class="checkbox" />
  </div>
<?php
}
function InstallPlugin_mz_PublishRND()
{
  global $zbp;
  if (!$zbp->HasConfig('mz_PublishRND')) {
    $zbp->Config('mz_PublishRND')->version = 1;
    $zbp->Config('mz_PublishRND')->odds = 37;
    $zbp->SaveConfig('mz_PublishRND');
  }
}
function UninstallPlugin_mz_PublishRND()
{
}
