<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {
  $zbp->ShowError(6);
  die();
}
if (!$zbp->CheckPlugin('mz_PublishRND')) {
  $zbp->ShowError(48);
  die();
}

if (count($_POST) > 0) {
  CheckIsRefererValid();
}
InstallPlugin_mz_PublishRND();
$enCodeFeed = urlencode("{$zbp->host}feed.php");

$blogtitle = '「随机版」自动发布';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle; ?></div>
  <div class="SubMenu">
    <a href="main.php" title="首页"><span class="m-left m-now">首页</span></a>
    <?php require "about.php"; ?>
  </div>
  <div id="divMain2">
    <form action="<?php echo BuildSafeURL("main.php?act=save"); ?>" method="post">
      <table width="100%" class="tableBorder">
        <tr>
          <th width="15%">项目</th>
          <th>内容</th>
          <th width="45%">说明</th>
        </tr>
        <tr>
          <td>「随机」几率：</td>
          <td><?php zbpform::text("odds", $zbp->Config("mz_PublishRND")->odds, "90%"); ?></td>
          <td><b><?php echo $zbp->Config("mz_PublishRND")->odds; ?></b> 天内的某一天会转为发布状态，具体将结合文章 ID 等参数运算决定；</td>
        </tr>
        <tr>
          <td></td>
          <td colspan="2"><input type="submit" value="提交" /></td>
        </tr>
      </table>
    </form>
    <p>访问触发：<?php echo mz_PublishRND_a($zbp->feedurl, "RSS 地址"); ?></p>
    <p>关于 RSS：<a href="https://bbs.zblogcn.com/thread-100631.html#488597" target="_blank" title="Z-Blog相关资讯RSS订阅-博友杂谈-ZBlogger技术交流中心">https://bbs.zblogcn.com/thread-100631.html#488597</a></p>
    <p>订阅：<?php echo mz_PublishRND_a("https://feeds.pub/feed/{$enCodeFeed}", "订阅"); ?></p>
    <p>订阅：<?php echo mz_PublishRND_a("https://www.innoreader.com/feed/{$enCodeFeed}", "订阅"); ?></p>
    <hr>
    <p><b>注：「随机」机制可能和你的预想不一样；</b></p>
  </div>
</div>

<?php
function mz_PublishRND_a($href, $title, $text = "")
{
  if (empty($text)) {
    $text = $href;
  }
  return "<a target=\"_blank\" href=\"{$href}\" title=\"{$title}\">$text</a>";
}
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
