<?php

namespace Api\WebSocket\sistemaChat;
use conversasDoUsuario;

$idsConversa = conversasDoUsuario($conn);

?>

<script>
  // Coloque os IDs das conversas em uma variável JavaScript
  var conversasDoUsuario = <?php echo json_encode($idsConversas); ?>;
  console.log(conversasDoUsuario);
</script>