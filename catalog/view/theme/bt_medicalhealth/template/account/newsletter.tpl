<?php echo $header; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
<?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content" class="eleven columns clearfix">
<div class="codespot-content main-column-content">
<?php echo $content_top; ?>
  <h1><?php echo $heading_title; ?></h1>
  <form class="newsletter_acc" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
    <div class="content">
      <table class="form">
        <tr>
          <td><?php echo $entry_newsletter; ?></td>
          <td><?php if ($newsletter) { ?>
            <input type="radio" name="newsletter" value="1" checked="checked" />
            <?php echo $text_yes; ?>&nbsp;
            <input type="radio" name="newsletter" value="0" />
            <?php echo $text_no; ?>
            <?php } else { ?>
            <input type="radio" name="newsletter" value="1" />
            <?php echo $text_yes; ?>&nbsp;
            <input type="radio" name="newsletter" value="0" checked="checked" />
            <?php echo $text_no; ?>
            <?php } ?></td>
        </tr>
      </table>
    </div>
    <div class="buttons">
      <div class="left"><a href="<?php echo $back; ?>" class="gray_button"><span><?php echo $button_back; ?></span></a></div>
      <div class="left"><span class="orange_button"><input type="submit" value="<?php echo $button_continue; ?>" class="button" /></span></div>
    </div>
  </form>
  <?php echo $content_bottom; ?></div></div>
<?php echo $footer; ?>