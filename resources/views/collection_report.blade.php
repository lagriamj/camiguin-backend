<!DOCTYPE html>
<html>
<head>
    <title>Collection Destination Report</title>
</head>
<style>
    * {
        font-family: Arial, sans-serif;
    }
</style>
<body>
   <div style="display: flex; flex-direction: column; width: 100%; text-align: center;">
     {{-- <p style="font-weight: bold">e-Ticketing System Report</p> --}}
     <span>{{ \Carbon\Carbon::parse($report[0]['start_date'])->format('F d, Y') }} - {{ \Carbon\Carbon::parse($report[0]['end_date'])->format('F d, Y') }}</span><br>
     <span style="font-weight: bold">{{ $report[0]['destination_name'] }}</span>
     <h2>Collection Report</h2>
   </div>

   <table style="width: 100%;">
    <thead>
        <tr style="font-size: 14px;">
            <th style="vertical-align: middle;">Book Date</th>
            <th style="vertical-align: middle;">Name</th>
            <th style="vertical-align: middle;">Tourist</th>
            <th style="vertical-align: middle;">Local</th>
            <th style="vertical-align: middle;">Student/PWD/5 years old below</th>
            <th style="vertical-align: middle;">Type</th>
            <th style="vertical-align: middle;">Payment</th>
            <th style="vertical-align: middle;">OR NO</th>
            <th style="vertical-align: middle;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($report as $item)
            @if (!isset($item['start_date'])) <!-- Exclude overall collection -->
            <tr style="font-size: 14px;">
                <td style="vertical-align: middle;">{{ \Carbon\Carbon::parse($item['book_date'])->format('F d, Y g:i a') }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ strtoupper($item['name']) }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $item['tourist'] }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $item['local'] }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $item['special'] }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $item['type'] }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $item['payment'] }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $item['or_num'] }}</td>
                <td style="text-align: center; vertical-align: middle;">PHP {{ number_format($item['amount'], 2) }}</td>

            </tr>
            @endif
        @endforeach
    </tbody>
</table>


    <div style="display: flex; flex-direction: column; text-align: right; padding-right: 30%; margin-top: 60px;">
        <p>Total Collection without OR: PHP{{ number_format($report->first()['total_collection_without_or'], 2) }}</p>
        <p>Total Collection with OR: PHP {{ number_format($report->first()['total_collection_with_or'], 2) }}</p>
        <p>Overall Collection: PHP{{ number_format($report->first()['overall_collection'], 2) }}</p>
    </div>
</body>
</html>
