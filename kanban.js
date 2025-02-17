$(document).ready(function() {
  // Modal Agregar
  $("#modalAgregar").hide();
  $(".botonAbrirModal").click(function() {
    $("#modalAgregar").fadeIn();
  });
  $(".cerrarModal").click(function() {
    $("#modalAgregar").fadeOut();
  });
  $(window).click(function(event) {
    if ($(event.target).is("#modalAgregar")) {
      $("#modalAgregar").fadeOut();
    }
  });
  
  // Modal Editar
  $("#modalEditar").hide();
  $(".cerrarModalEditar").click(function() {
    $("#modalEditar").fadeOut();
  });
  $(window).click(function(event) {
    if ($(event.target).is("#modalEditar")) {
      $("#modalEditar").fadeOut();
    }
  });
  
  // Hacer las tarjetas arrastrables
  $(".tarjeta").draggable({
    revert: "invalid",
    zIndex: 100,
    containment: "document",
    cursor: "move"
  });
  
  // Zonas droppables (columnas)
  $(".zonaTareas").droppable({
    accept: function(draggable) {
      let tarjeta = $(draggable);
      let columnaOrigen = tarjeta.parent().parent().attr("id");
      let columnaDestino = $(this).parent().attr("id");
      
      // Si el destino es "ideas", solo se permite si la tarjeta ya está en "ideas"
      if (columnaDestino === "ideas" && columnaOrigen !== "ideas") {
        return false;
      }
      // Mover a "done" solo permitido si la tarjeta proviene de "doing"
      if (columnaDestino === "done" && columnaOrigen !== "doing") {
        return false;
      }
      return true;
    },
    drop: function(event, ui) {
      let tarjeta = $(ui.draggable);
      let columnaDestino = $(this).parent().attr("id");
      
      if (tarjeta.parent().parent().attr("id") === "ideas") {
        tarjeta.data("salioDeIdeas", true);
      }
      
      $(this).append(tarjeta);
      tarjeta.css({ top: "auto", left: "auto" });
      
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
  
  // Toggle del contenido interno de la tarjeta
  $(".tarjeta").click(function(e) {
    if ($(e.target).is('.inputNota, .botonAgregarNota, .botonEditar')) {
      return;
    }
    var content = $(this).find(".tarjetaContenido");
    content.toggle();
    var editButton = $(this).find(".botonEditar");
    if (content.is(":visible")) {
      editButton.hide();
    } else {
      editButton.show();
    }
  });
  
  // Agregar nueva nota
  $(document).on("click", ".botonAgregarNota", function(e) {
    e.stopPropagation();
    let tarjeta = $(this).closest(".tarjeta");
    let tarjetaId = tarjeta.attr("data-id");
    let inputNota = tarjeta.find(".inputNota");
    let mensaje = inputNota.val();
    let notaContainer = tarjeta.find(".notas");
    
    if (mensaje.trim() !== "") {
      $.ajax({
        url: "agregarNota.php",
        type: "POST",
        data: { id: tarjetaId, mensaje: mensaje },
        success: function(response) {
          notaContainer.append("<div class='mensaje'>" + response + "</div>");
          inputNota.val("");
        },
        error: function() {
          console.error("Error al agregar la nota.");
        }
      });
    }
  });
  
  // Manejo del botón Editar
  $(document).on("click", ".botonEditar", function(e) {
    e.stopPropagation();
    var id = $(this).data("id");
    var nombre = $(this).data("nombre");
    var autor = $(this).data("autor");
    var colaboradores = $(this).data("colaboradores");
    if (typeof colaboradores === "string") {
      colaboradores = JSON.parse(colaboradores);
    }
    // Rellenar el formulario de editar
    $("#editId").val(id);
    $("#editNombre").val(nombre);
    
    // Rellenar el contenedor de colaboradores en el modal de edición
    var container = $("#editColaboradores");
    container.empty();

    // Recorrer la lista global de usuarios
    $.each(usuariosList, function(index, usuario) {
      if (usuario.nombre === autor || usuario.nombre === "admin") return;
      var checked = "";
      if (Array.isArray(colaboradores) && colaboradores.indexOf(usuario.nombre) !== -1) {
        checked = "checked";
      }
      var checkbox = '<label><input type="checkbox" name="colaboradores[]" value="'+ usuario.nombre +'" '+ checked +'> '+ usuario.nombre +'</label>';
      container.append(checkbox);
    });
    
    $("#modalEditar").fadeIn();
  });
});
