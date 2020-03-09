<div class="chae-wrapper">
  <div style="text-align: right">
    <a class="wp-block-button__link has-background has-vivid-cyan-blue-background-color" href="#kontakt">Abmelden</a>
  </div>
  <div class="chae-public-content">
    <h3><b>Chäschtli <?php echo $stufenName ?></b></h3>
    <?php if($expired){ ?>
      <p>Keine aktuellen Informationen verfügbar.</p>
    <?php } else { ?>
      <h6>Treffpunkt</h6><p><?php echo $zeit ?><br><?php echo $chaeschtli->wo ?></p>
      <h6>Infos</h6><p><?php echo nl2br($chaeschtli->infos) ?></p>
      <h6>Mitnehmen</h6><p><?php echo nl2br($chaeschtli->mitnehmen) ?></p>
    <?php }; ?>
  </div>
</div>
