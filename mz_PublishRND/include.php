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
  // $curDate = (int)date('Ymd');
  $curTime = time();
  $curDays = intval($curTime / 86400);
  $odds = $zbp->Config('mz_PublishRND')->odds;
  $list = mz_PublishRND_GetPostList($odds);
  // 获取到的文章数量
  $cntList = count($list);
  mz_PublishRND_Log("count - {$cntList}");
  foreach ($list as $article) {
    $log = "{$article->ID} - {$article->Url} - #tip#";
    if (!mz_PublishRND_Check($article)) {
      // 状态为审核但是没有设置发布标记
      // $tip = "需要设置发布标记";
      $tip = "need set publish tag ------";
      mz_PublishRND_Log(str_replace("#tip#", $tip, $log));
      mz_PublishRND_SetHint($article->Title, $article->ID);
      continue;
    }
    // 发布
    if (($curDays + $article->ID) % $odds == 0) {
      // $tip = "发布成功";
      $tip = "publish success ++++++";
      mz_PublishRND_Log(str_replace("#tip#", $tip, $log));
      $article->Status = 0;
      $article->Save();
    } else {
      // $tip = "等待发布";
      $tip = "wait publish";
      mz_PublishRND_Log(str_replace("#tip#", $tip, $log));
    }
  }
}

// 获取审核状态的文章
function mz_PublishRND_GetPostList($num)
{
  global $zbp;
  $w = array();
  $w[] = array('=', 'log_Type', 0);
  $w[] = array('=', 'log_Status', 2);
  $order = array(
    'log_ID' => 'ASC'
  );
  $Posts = $zbp->GetPostList('*', $w, $order, $num * 4, null);
  return $Posts;
}

// 添加标记
function mz_PublishRND_post(&$article)
{
  // global $zbp;
  if (GetVars('edtPubRND', 'POST') != 1) {
    return;
  }
  $article->Status = 2;
  $article->Content = trim(str_replace("<!--PubRND-->", "", $article->Content));
  $article->Content .= "\n\n<!--PubRND-->";
}

// 检查文章内标记
function mz_PublishRND_Check($article)
{
  if (false !== strpos($article->Content, "<!--PubRND-->")) {
    return true;
  }
  if ($article->Metas->HasKey('md_content') && false !== strpos($article->Metas->md_content, "<!--PubRND-->")) {
    return true;
  }
  return false;
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

function mz_PublishRND_Log($msg)
{
  $msg = "msg: {$msg}\n";
  $logFile = mz_PublishRND_Path("log.txt");
  $logCon = file_get_contents($logFile);
  $logCon = str_replace($msg, "", $logCon);
  file_put_contents($logFile, $logCon . $msg);
}

function mz_PublishRND_SetHint($title, $id)
{
  global $zbp;
  // 文章编辑地址
  $edtUrl = $zbp->host . "zb_system/admin/edit.php?act=ArticleEdt&id={$id}";
  $edtLink = "<a target=\"_blank\" href=\"{$edtUrl}\">{$title}</a>";
  $zbp->SetHint("tips  hint_always", "「随机发布」文章未设置发布标记：{$edtLink}");
}

function mz_PublishRND_Path($file, $t = 'path')
{
  global $zbp;
  $result = $zbp->$t . 'zb_users/plugin/mz_PublishRND/';
  switch ($file) {
    case 'log.txt':
      return $result . 'logs/log.txt';
      break;
    case 'main':
      return $result . 'main.php';
      break;
    default:
      return $result . $file;
  }
}

function InstallPlugin_mz_PublishRND()
{
  global $zbp;
  if (!$zbp->HasConfig('mz_PublishRND')) {
    $zbp->Config('mz_PublishRND')->version = "1.1";
    $zbp->Config('mz_PublishRND')->odds = 37;
    $zbp->SaveConfig('mz_PublishRND');
  }
  $dir = mz_PublishRND_Path("logs");
  if (!is_dir($dir)) {
    @mkdir($dir, 0755);
  }
  $file = mz_PublishRND_Path("log.txt");
  if (!is_file($file)) {
    file_put_contents($file, "");
  }
}

// 插件升级时执行
function UpdatePlugin_mz_PublishRND()
{
  global $zbp;
  $version = $zbp->Config('mz_PublishRND')->version;
  if (version_compare($version, '1.1', "<")) {
    $zbp->Config('mz_PublishRND')->version = '1.1';
    InstallPlugin_mz_PublishRND();
    $zbp->SaveConfig('mz_PublishRND');
  }
}

// 旧版兼容
function mz_PublishRND_Updated()
{
  UpdatePlugin_mz_PublishRND();
}

function UninstallPlugin_mz_PublishRND()
{
}
