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

$act = GetVars('act', 'GET');
$suc = GetVars('suc', 'GET');
if ($act == 'save') {
  CheckIsRefererValid();
  foreach ($_POST as $key => $val) {
    if (substr($key, 0, 5) == 'read_') {
      continue;
    }
    $zbp->Config('mz_PublishRND')->$key = trim($val);
  }
  $zbp->SaveConfig('mz_PublishRND');
  $zbp->BuildTemplate();
  $zbp->SetHint('good');
  Redirect('./main.php' . ($suc == null ? '' : "?act={$suc}"));
}

if ($act == 'delLog') {
  CheckIsRefererValid();
  $file = mz_PublishRND_Path("log.txt");
  if (is_file($file)) {
    unlink($file);
  }
  $zbp->SetHint('good');
  Redirect('./main.php' . ($suc == null ? '' : "?act={$suc}"));
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
    <p>访问触发：<?php echo mz_PublishRND_a($zbp->feedurl, "RSS 地址", "", "js-reload"); ?></p>
    <p>关于 RSS：<a href="https://bbs.zblogcn.com/thread-100631.html#488597" target="_blank" title="Z-Blog相关资讯RSS订阅-博友杂谈-ZBlogger技术交流中心">https://bbs.zblogcn.com/thread-100631.html#488597</a></p>
    <p>订阅：<?php echo mz_PublishRND_a("https://feeds.pub/feed/{$enCodeFeed}", "订阅"); ?></p>
    <p>订阅：<?php echo mz_PublishRND_a("https://www.innoreader.com/feed/{$enCodeFeed}", "订阅"); ?></p>
    <hr>
    <p><b>注：「随机」机制可能和你的预想不一样；</b></p>
    <p>日志中「need set publish tag」表示该文章是审核状态，但是未设置自动发布；</p>
    <p>
      查看日志：<?php echo mz_PublishRND_a(mz_PublishRND_Path("log.txt", "host"), "查看日志", "logs/log.txt"); ?> - 「 <?php echo mz_PublishRND_a(BuildSafeURL("main.php?act=delLog"), "删除日志", "删除日志", "", "_self"); ?>」
    </p>
  </div>
</div>

<script>
  // 点击链接时刷新当前页面
  $(function() {
    $(".js-reload").on('click', function() {
      $(this).data("reload", 1);
    }).mouseout(function() {
      if ($(this).data("reload") == 1) {
        window.location.reload();
      }
    });
  })
</script>

<?php
function mz_PublishRND_a($href, $title, $text = "", $class = "mz-PubRnd", $target = "_blank")
{
  if (empty($text)) {
    $text = $href;
  }
  return "<a class=\"{$class}\" target=\"{$target}\" href=\"{$href}\" title=\"{$title}\">$text</a>";
}
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
