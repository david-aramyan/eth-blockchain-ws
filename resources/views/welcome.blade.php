<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap CSS --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Blockchain WS</title>

    <script>
        window.laravel_echo_port='6001';
    </script>
</head>
<body>


<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <h2>Blocks</h2>
            <div class="table-responsive">
                <table class="table table-bordered block">
                    <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <h2>Transactions</h2>
            <div class="table-responsive">
                <table class="table table-bordered transaction">
                    <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Block ID</th>
                        <th>To Address</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- jQuery first, then Popper.js, then Bootstrap JS --}}
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
{{-- Socket server --}}
<script src="//{{ Request::getHost() }}:6001/socket.io/socket.io.js"></script>
<script src="{{ url('/js/laravel-echo-setup.js') }}" type="text/javascript"></script>

<script>
    window.Echo.channel('laravel_database_tx-channel')
        .listen('TransactionEvent', function (data) {
            let transaction = "<tr><td>" + data.transaction.txId + "</td><td>" + data.transaction.blockId + "</td><td>" + data.transaction.toAddress +"</td></tr>";
            $(".transaction tbody").append(transaction);

        });
    window.Echo.channel('laravel_database_block-channel')
        .listen('BlockEvent', function (data) {
            let block = "<tr><td>" + data.block + "</td></tr>";
            $(".block tbody").append(block);
        });


</script>
</body>
</html>
