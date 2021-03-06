<style type="text/css">
	li{
		list-style: none;
	}
</style>
<script type="text/javascript">
	$(document).ready(function(){

		$("#add_product_btn").click(function(){
			$('#add_product_modal').openModal();	
		});
		$("input[name=people]").keyup(function(){
			if($(this).val() != ''){
				$.ajax({
					url: "<?php echo site_url('/StockController/searchPeople')?>",
					type: "POST",
					cache: false,
					data: {name_people: $(this).val()},
					success: function(data){
						$('#loadPeople').html("");
						var obj = JSON.parse(data);
						if(obj.length>0){
							try{
								var items=[]; 	
								$.each(obj, function(i,val){											
									items.push($("<option  id="+ val.id_people +">"+ val.name +"</option>"));
								});	
								$('#loadPeople').append.apply($('#loadPeople'), items);
							}catch(e) {		
								alert('Ocorreu algum erro ao carregar os Fornecedores!');
							}		
						}else{
							$('#loadPeople').html($('<span/>').text("Nenhum Fornecedor encontrado!"));		
						}		
					},
					error: function(data){
						alert("Ocorreu algum erro ao carregar os Fornecedores");
					}
				});
			}else{
				$('#loadPeople').html(" ");
			}
		});

		$("#loadPeople").on("click", "option", function(){
			$("input[name=people]").val("");
			$("#people").html($(this));
			$('#loadPeople').html(" ");
		});

		$("input[name=product_name]").keyup(function(){
			if($(this).val() != ''){
				$.ajax({
					url: "<?php echo site_url('/StockController/searchProductStock')?>",
					type: "POST",
					cache: false,
					data: {name_product: $(this).val()},
					success: function(data){
						$('#loadProduct').html("");
						var obj = JSON.parse(data);
						if(obj.length>0){
							try{
								var items=[]; 	
								$.each(obj, function(i,val){											
									items.push($("<option name="+val.unit_price+" id="+ val.id_product +">"+ val.name_product +"</option>"));
								});	
								$('#loadProduct').append.apply($('#loadProduct'), items);
							}catch(e) {		
								alert('Ocorreu algum erro ao carregar os Produto!');
							}		
						}else{
							$('#loadProduct').html($('<span/>').text("Nenhum Produto encontrado!"));
						}		
					},
					error: function(data){
						alert("Ocorreu algum erro ao carregar os Produtos");
					}
				});
			}else{
				$('#loadProduct').empty();
			}
		});
		$("#loadProduct").on("click", "option", function(){
			$("input[name=product_name]").val("");
			$("#product").html($(this));
			$("#price_product").html("Valor: "+$(this).attr("name"));
			$('#loadProduct').empty();
		});
		var total = 0;
		$("#add_product_output_btn").click(function(e){
			e.preventDefault();
			var check = false;
			if($('#product').is(':empty')){
				Materialize.toast("Selecione algum produto", 4000);
				check = true;
			}else{
			var id = $("#product option").attr("id");
			$("tbody td").each(function(i){
				if( id == $(this).attr("id")){
					check = true;
					Materialize.toast("Este produto já foi importado para a tabela", 4000);
				}
			});
			}
			if(!check){
				$("#add_product_modal").closeModal();
				
				var newRow = $("<tr class='productRow'>");
				var cols = "";

				cols += '<td class="tdProductId" id='+ $("#product option").attr("id") +'>'+ $("#product option").text() +'</td>';
				cols += '<td class="tdProductAmount">'+ $("input[name=amount]").val() +'</td>';
				cols += '<td class="tdProductPrice">'+'R$ '+ $("#product option").attr("name") +'</td>';
				cols += '<td class="tdProductTotal">'+'R$ '+ ($("#product option").attr("name") * $("input[name=amount]").val()).toFixed(2) +'</td>';
				cols += '<td>';
				cols += '<a href="#" class="removeProduct">Remover</a>';
				cols += '</td>';

				newRow.append(cols);
				$("#output_stock_product").append(newRow);
				
				total = total + ($("#product option").attr("name") * $("input[name=amount]").val());
				
				$("#total").html("Total: R$" +total.toFixed(2));
				$("input[name=amount]").val("");
				$("#price_product").empty();
				$("input[name=product_name]").val("");
				$('#loadProduct').empty();
				$('#product').empty();
				
			}
			
		});
		$("#output_stock_product").on("click", ".removeProduct", function(e){
			
			e.preventDefault();
			var $this = $(this);
			var valueRemove = $this.parents("tr").find(".tdProductTotal").text().replace(/[^0-9.,]/g,'');
			total = total - valueRemove;
			$("#total").html("Total: R$ "+ total);
			$(this).parents('tr').remove();
		});
		$("#add_output_stock_btn").click(function(e){
			e.preventDefault();
			$("#add_output_stock_btn").attr("disabled", true);
			
			var productsData = [];
			$(".productRow").each(function(i){
				var pData = { 
					id_product: $(this).find(".tdProductId").attr("id"),
					amount:  $(this).find(".tdProductAmount").text(),
					price: Number($(this).find(".tdProductPrice").text().replace(/[^0-9.,]/g,''))
				};
				productsData.push(pData);
			});

			$.ajax({
				url: "<?php echo site_url('/StockController/createOutputStock'); ?>",
				type: "POST",
				data: {
					id_people: $("#people option").attr("id"),
					descript: $("input[name=descript]").val(),
					date: $("input[name=date]").val(),
					products: JSON.stringify(productsData)
				},
				success: function(data) {
					if ($.isNumeric(data)) {
						$("#add_output_stock_btn").attr("disabled", false); 
						document.location.href = "<?= base_url('stock/outputs/'); ?>/" + data;
					}
					else {
						Materialize.toast(data, 4000);
						$("#add_output_stock_btn").attr("disabled", false); 
					}
				},
				error: function(data){
					console.log(data);
					Materialize.toast('Erro ao adicionar uma nova saída de estoque!', 4000);
					$("#add_output_stock_btn").attr("disabled", false); 
				}
			});
		});
	});
</script>
<div class="container">
	<div class="row">
		<div class="">
			<a href="<?=base_url('stock/outputs') ?>">< Voltar para saídas</a>
			<h4>Nova saída</h4>
			<div class="card-panel col s12 m12 l8">
				<div class="input-field col s12 m12">
					<input name="people" type="text" autocomplete="off" maxlength="45" required placeholder="Pessoa...">
				</div>
				<div class="">
					<a href="#" id="loadPeople" class="col s12 m12"></a>
					<h5 id="people" class="col s12"></h5>
				</div>
				<div class="input-field margin-alter col s12 m6">
					<input name="descript" type="text" maxlength="250" placeholder="Descrição...">
				</div>
				<div class="col s12 m6">
					<label for="date" class="black-text">Data de saída:</label>
					<input placeholder="Data" name="date" type="date" required>
				</div>
			</div>
			<div class="container col s12 m12 l3">
				<button id="add_output_stock_btn" type="submit" class="green btn-large">Finalizar<i class="material-icons right">send</i></button>
			</div>
		</div>

		<div class="card-panel col s12 m12 l10">
			<div class="col s12 m6">
				<p><a id="add_product_btn" class="btn green">Adicionar produto</a></p>
			</div>
			<div class="right-align col s12 m6">
				<p id="total" class="btn grey" disabled>Total: R$</p>
			</div>
		</div>

		<div class="col s12 m12 l10 collection responsive-table">
			<table  class="bordered highlight">
				<thead>
					<td><strong>Produto</strong></td>
					<td><strong>Quantidade</strong></td>
					<td><strong>Valor unitário</strong></td>
					<td><strong>Valor total</strong></td>
					<td><strong>Ações</strong></td>
				</thead>
				<tbody id="output_stock_product">

				</tbody>
			</table>
		</div>
	</div>

	<div id="add_product_modal" class="modal">
		<form>
			<div class="modal-content row bodyModal">
				<h4>Adicionar produto</h4>
				<div class="input-field col s12 m4">	
					<input name="product_name" autocomplete="off" type="text" maxlength="45" placeholder="Produto">
					<div id="products" class="col s12 m12">
						<a href="#" id="loadProduct" class="col s6"></a>
						<h5 id="product" class="col s12 m6"></h5>
					</div>
				</div>
				<div class="input-field col s12 m2">
					<input name="amount" required="required" type="number" placeholder="Quantia">
				</div>
				<div class="input-field col s12 m2">
					<p class="chip" id="price_product"></p>
				</div>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn-flat modal-action modal-close">Cancelar</a>
				<button id="add_product_output_btn" type="submit" class="btn green">Adicionar<i class="material-icons right">send</i> </button>
			</div>
		</form>
	</div>
</div>