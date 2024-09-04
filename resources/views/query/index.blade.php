@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="float-start">
                    SQL Query
                </div>
                <div class="float-end">
                    <a href="{{ route('home') }}" class="btn btn-primary btn-sm">&larr; Back</a>
                </div>
            </div>
            <div class="card-body">
                <form id="queryForm" action="{{ route('dev.store') }}" method="post">
					@csrf
					<div class="mb-3 row">
                        <label for="sql" class="col-md-4 col-form-label text-md-end text-start">Raw SQL</label>
                        <div class="col-md-6">
							<textarea type="text" class="form-control @error('sql') is-invalid @enderror" id="sql" name="sql" value="{{ old('sql') }}"></textarea>
							<span class="text-danger"></span>
                        </div>
                    </div>
					<div class="mb-1 row">
                        <input type="submit" class="col-md-3 offset-md-5 btn btn-primary" name="execute" value="Execute">
					</div>
					<div class="mb-1 row">
						<input type="submit" class="col-md-3 offset-md-5 btn btn-primary" name="excel" value="Export Excel" disabled>	
					</div>
					<div class="mb-1 row">
                        <input type="submit" class="col-md-3 offset-md-5 btn btn-primary" name="json" value="Export Json" disabled>
                    </div>		
				</form>
			</div>
		</div>
		
		<div class="card">
            <div class="card-header">
                <div class="float-start">
                    Query Result
                </div>
            </div>
            <div class="card-body">
                <table id="result" class="table table-striped table-bordered">
				</table>
			</div>
		</div>
    </div>
</div>

<link href="{{ asset('dataTables.dataTables.min.css') }}" rel="stylesheet">
<script src="{{ asset('jquery.min.js') }}"></script>
<script src="{{ asset('dataTables.min.js') }}"></script>
<script type="text/javascript">

	$(function(){
		$('input[name="execute"]').click(function(e){
			e.preventDefault();
			let btn = $(this);
			btn.attr('disabled', 'disabled');
			$.ajax({
				url: "{{ route('dev.store') }}",
				type: 'POST',
				dataType: 'json',
				data: $('#queryForm').serialize(),
				success: function(d){
					btn.removeAttr('disabled');
					if(d.status == true){
						$('input[type="submit"]').removeAttr('disabled');
					        $('.text-danger').html('');
						let columns = [];
						$.each(d.columns, function(index, item){
							columns.push({'data': item});
						});
						let _t = new DataTable('#result', {
							data: d.data,
							columns: columns,
							paging: false,
							info: false,
							searching: false,
							destroy: true,
							draw: 1
						});
					}else{
						$('.text-danger').html(d.msg);
					}
				}
			});
		});
	})

</script>
@endsection
