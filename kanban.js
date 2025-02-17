$(document).ready(function() {
    // Inicialmente se oculta el modal de agregar tarjeta
    $("#modalAgregar").hide();
  
    // Abrir el modal al hacer clic en el botón "Añadir Idea"
    $(".botonAbrirModal").click(function() {
      $("#modalAgregar").fadeIn();
    });
  
    // Cerrar el modal al hacer clic en la "X"
    $(".cerrarModal").click(function() {
      $("#modalAgregar").fadeOut();
    });
  
    // Cerrar el modal si se hace clic fuera del contenido del modal
    $(window).click(function(event) {
      if ($(event.target).is("#modalAgregar")) {
        $("#modalAgregar").fadeOut();
      }
    });
  
    // Hacer las tarjetas arrastrables
    $(".tarjeta").draggable({
      revert: "invalid",          // Vuelve a su posición si no se suelta en un área válida
      zIndex: 100,                // Se muestra por encima de otros elementos mientras se arrastra
      containment: "document",    // Restringe el movimiento dentro del documento
      cursor: "move"              // Cambia el cursor para indicar que se puede mover
    });
  
    // Definir las zonas droppables (áreas donde se pueden soltar tarjetas)
    $(".zonaTareas").droppable({
      accept: function(draggable) {
        let tarjeta = $(draggable);
        let columnaOrigen = tarjeta.parent().parent().attr("id");
        let columnaDestino = $(this).parent().attr("id");
  
        // Restricción: No permitir mover a "ideas" si la tarjeta ya salió
        if (columnaDestino === "ideas" && tarjeta.data("salioDeIdeas")) {
          return false;
        }
        // Restricción: Solo permitir mover a "done" si la tarjeta proviene de "doing"
        if (columnaDestino === "done" && columnaOrigen !== "doing") {
          return false;
        }
        return true;
      },
      drop: function(event, ui) {
        let tarjeta = $(ui.draggable);
        let columnaDestino = $(this).parent().attr("id");
  
        // Si la tarjeta sale de "ideas", marcarla para que no pueda volver
        if (tarjeta.parent().parent().attr("id") === "ideas") {
          tarjeta.data("salioDeIdeas", true);
        }
  
        // Agregar la tarjeta a la nueva columna y reajustar su posición
        $(this).append(tarjeta);
        tarjeta.css({ top: "auto", left: "auto" });
  
        // Actualizar el estado de la tarjeta en la base de datos vía AJAX
        let tarjetaId = tarjeta.attr("data-id");
        $.ajax({
          url: "actualizarEstado.php",
          type: "POST",
          data: { id: tarjetaId, estado: columnaDestino },
          success: function(response) {
            console.log("Estado actualizado correctamente.");
          },
          error: function() {
            console.error("Error al actualizar el estado.");
          }
        });
      }
    });
  });
  