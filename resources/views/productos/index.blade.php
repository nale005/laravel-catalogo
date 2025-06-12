<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Catálogo de Productos</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body class="p-4">

        <h1>Catálogo de Productos</h1>
        <button class="btn btn-primary mb-3" id="btnAddProduct">Agregar Producto</button>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Cantidad</th>
                    <th>Foto</th>
                    <th>Precio</th>
                    <th id="thIngreso" style="cursor:pointer;">Fecha de Ingreso &#9650;</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableProductoBody">
                
            </tbody>
        </table>

        <nav>
            <ul class="pagination" id="pagination">
            </ul>
        </nav>

        <!-- Modal para agregar o editar -->
        <div class="modal fade" id="productoModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="alert alert-danger d-none" id="formErrors"></div>
                    <form id="productoForm">
                        <div class="modal-header">
                            <h5 class="modal-title">Producto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="productoId">
                            <div class="mb-3">
                                <label>Código</label>
                                <input type="text" id="codigo" name="codigo" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Nombre</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Cantidad</label>
                                <input type="number" id="stock" name="stock" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Foto</label>
                                <input type="file" id="foto" name="foto" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label>Precio</label>
                                <input type="number" step="0.01" id="precio" name="precio" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Fecha de Ingreso</label>
                                <input type="date" id="ingreso" name="ingreso" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Fecha de Vencimiento</label>
                                <input type="date" id="expira" name="expira" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Guardar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para eliminar -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ¿Está seguro que desea eliminar este producto?
                        <input type="hidden" id="deleteProductoId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let currentPage = 1;
            let direccion = 'asc';

            // Función de cargar los productos.
            function cargarProductos(page = 1) {
                $.get('/productos/list', { page: page, sort: 'ingreso', direction: direccion }, function(data) {
                    let rows = '';
                    data.data.forEach(producto => {
                        rows += `<tr>
                            <td>${producto.codigo}</td>
                            <td>${producto.nombre}</td>
                            <td>${producto.stock}</td>
                            <td>${producto.foto ? '<img src="/uploads/' + producto.foto + '" width="50">' : ''}</td>
                            <td>${producto.precio}</td>
                            <td>${producto.ingreso}</td>
                            <td>${producto.expira}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editarProducto(${producto.id})">Editar</button>
                                <button class="btn btn-sm btn-danger" onclick="showDeleteModal(${producto.id})">Eliminar</button>
                            </td>
                        </tr>`;
                    });
                    $('#tableProductoBody').html(rows);

                    let pagination = '';
                    for (let i = 1; i <= data.last_page; i++) {
                        pagination += `<li class="page-item ${i == data.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="cargarProductos(${i})">${i}</a>
                        </li>`;
                    }
                    $('#pagination').html(pagination);
                    currentPage = data.current_page;
                });
            }

            // Iniciar las funciones.
            $(document).ready(function() {
                cargarProductos();

                // Cambiar orden.
                $('#thIngreso').click(function() {
                    sortDirection = (sortDirection == 'asc') ? 'desc' : 'asc';
                    cargarProductos(currentPage);
                });

                // Abrir modal para agregar.
                $('#btnAddProduct').click(function() {
                    $('#productoForm')[0].reset();
                    $('#productoId').val('');
                    $('#productoModal').modal('show');
                });

                // Guardar el producto.
                $('#productoForm').submit(function(e) {
                    e.preventDefault();

                    // Validaciones en front.
                    $('#formErrors').addClass('d-none').html('');
                    const codigo = $('#codigo').val();
                    const codigoRegex = /^[a-zA-Z0-9]+$/;
                    if (!codigoRegex.test(codigo)) {
                        $('#formErrors').html('El código de producto solo puede contener letras y números.').removeClass('d-none');
                        return;
                    }

                    const nombre = $('#nombre').val();
                    const nombreRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
                    if (!nombreRegex.test(nombre)) {
                        $('#formErrors').html('El nombre del producto solo puede contener letras.').removeClass('d-none');
                        return;
                    }

                    const fotoInput = $('#foto')[0];
                    if (fotoInput.files.length > 0) {
                        const file = fotoInput.files[0];
                        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                        if (!allowedTypes.includes(file.type)) {
                            $('#formErrors').html('La fotografía debe ser de tipo jpeg, jpg, png o gif.').removeClass('d-none');
                            return;
                        }

                        if (file.size > 1.5 * 1024 * 1024) {
                            $('#formErrors').html('La fotografía no debe exceder 1.5 MB.').removeClass('d-none');
                            return;
                        }
                    }

                    const ingreso = $('#ingreso').val();
                    const expira = $('#expira').val();
                    if (ingreso > expira) {
                        $('#formErrors').html('La fecha de ingreso no puede ser mayor que la fecha de vencimiento.').removeClass('d-none');
                        return;
                    }

                    let formData = new FormData(this);
                    let id = $('#productoId').val();
                    let url = id ? '/productos/' + id : '/productos';
                    let method = id ? 'POST' : 'POST';
                    if (id) formData.append('_method', 'PUT');
                    formData.append('_token', '{{ csrf_token() }}');

                    $.ajax({
                        url: url,
                        type: method,
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function() {
                            $('#productoModal').modal('hide');
                            cargarProductos(currentPage);
                        },
                        error: function(response) {
                            console.log(response);
                            let errors = response.responseJSON?.errors;
                            if (errors) {
                                let errorList = '';
                                $.each(errors, function(key, messages) {
                                    messages.forEach(function(msg) {
                                        errorList += `<div>${msg}</div>`;
                                    });
                                });
                                $('#formErrors').html(errorList).removeClass('d-none');
                            } else if (response.responseText) {
                                $('#formErrors').html(response.responseText).removeClass('d-none');
                            } else {
                                $('#formErrors').html('Error desconocido').removeClass('d-none');
                            }
                        }
                    });
                });

                // Confirmar eliminación.
                $('#confirmDelete').click(function() {
                    let id = $('#deleteProductoId').val();
                    $.ajax({
                        url: '/productos/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function() {
                            $('#deleteModal').modal('hide');
                            cargarProductos(currentPage);
                        }
                    });
                });
            });

            // Editar un producto.
            function editarProducto(id) {
                $('#productoForm')[0].reset();
                $.get('/productos/' + id, function(producto) {
                    $('#productoId').val(producto.id);
                    $('#codigo').val(producto.codigo);
                    $('#nombre').val(producto.nombre);
                    $('#stock').val(producto.stock);
                    $('#precio').val(producto.precio);
                    $('#ingreso').val(producto.ingreso);
                    $('#expira').val(producto.expira);
                    $('#productoModal').modal('show');
                });
            }

            // Mostrar modal eliminar.
            function showDeleteModal(id) {
                $('#deleteProductoId').val(id);
                $('#deleteModal').modal('show');
            }
        </script>

    </body>
</html>
