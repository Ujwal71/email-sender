<div class="alert alert-warning">
    <strong>{{ count($rows) }} row(s) were skipped:</strong>
    <div class="table-responsive mt-2" style="max-height: 250px; overflow:auto;">
        <table class="table table-sm table-borderless mb-0">
            <thead><tr><th>Line</th><th>Row</th><th>Reason</th></tr></thead>
            <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['line'] }}</td>
                    <td>{{ $row['value'] }}</td>
                    <td>{{ $row['reason'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
