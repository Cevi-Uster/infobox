<form action="" method="post" id="chae-dash-form" class="initial-form hide-if-no-js">


	<div class="input-text-wrap" id="title-wrap">
		<h3>Von</h3>
		<div class="chae-col-container">
			<div class="chae-col chae-col-50 chae-input-group">
		<input type="text" name="von-date" id="von-date" placeholder="Datum" autocomplete="off" data-toggle="datepicker" value="<?php echo $von->format('j.m.Y')?>">
	</div>
	<div class="chae-col chae-col-50 chae-input-group clockpicker">
		<input type="time" name="von-time" id="von-time" class="form-control" placeholder="Zeit" value="<?php echo $von->format('H:i')?>" autocomplete="off">
	</div>
</div>
</div>

  <div class="input-text-wrap" id="title-wrap">
		<h3>Bis</h3>
		<div class="chae-col-container">
			<div class="chae-col chae-col-50 chae-input-group">
    		<input type="text" name="bis-date" id="bis-date" placeholder="Datum" autocomplete="off" data-toggle="datepicker"  value="<?php echo $bis->format('j.m.Y')?>">
			</div>
			<div class="chae-col chae-col-50 chae-input-group clockpicker">
				<input type="time" name="bis-time" id="bis-time" class="form-control" placeholder="Zeit" value="<?php echo $bis->format('H:i')?>" autocomplete="off">
			</div>
		</div>
  </div>

	<div class="input-text-wrap chae-input-group" id="title-wrap">
		<h3>Wo</h3>
		<input type="text" name="wo" id="wo" placeholder="Wo"autocomplete="off" value="<?php echo $chaeschtli->wo ?>">
	</div>

	<div class="textarea-wrap chae-input-group" id="description-wrap">
		<h3>Infos / Mitnehmen</h3>
		<textarea name="content" id="infos" class="mceEditor" rows="3" cols="15" placeholder="Infos / Mitnehmen"autocomplete="off"><?php echo $chaeschtli->infos ?></textarea>
	</div>

	<p class="chae-last-update pull-right">Zuletzt aktualisiert: <?php echo $last_update ?>, Status: <?php echo $status ?></p>
	<p class="submit">
		<input type="hidden" name="action" value="SaveNewChaeschtli">
		<input type="hidden" name="stufe" value="<?php echo $chaeschtli->stufen_id ?>">
		<input type="submit" id="save-chaeschtli" class="button button-primary" value="Speichern">
	</p>

</form>
