<div class="sidebar">
  <div class="sidebarItem toggleSidebar">
    <img src="../resources/imgs/caret-left.svg" alt="Toggle Sidebar">
    <?php if (isset($_SESSION["user"])):?>
      <span id="Collapse" tabindex="0" role="button"><?= "Collapse"?></span>
      <span id="Unfold" style="display:none"><?= "Unfold"?></span>
    <?php else:?>
      <span id="Collapse" tabindex="0" role="button">Collapse</span>
      <span id="Unfold" style="display:none">Unfold</span>
    <?php endif?>
  </div>
  <hr>

  <?php if (isset($_SESSION['user'])): ?>
    <?php $options = [ "terminverwaltung", "forum"]?>

    <?php foreach ($options as $index => $option): ?>
        <div class="sidebarItem" title="<?= $option?>">
            <img src="../imgs<?= $icons[$index]; ?>" alt="<?= $option; ?>">
            <span role="button" tabindex="0">
                <a tabindex="-1" href="<?= $contents[$index]; ?>"><?= $option; ?></a>
            </span>
        </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script src="../resources/js/sidebar.js"></script>