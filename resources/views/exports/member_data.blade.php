<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #1E2A4A;
        }

        h2 {
            border-bottom: 2px solid #1E2A4A;
            padding-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        td {
            padding: 5px 0;
        }

        td.label {
            font-weight: bold;
            width: 200px;
        }
    </style>
</head>

<body>
    <h2>KPMPCATS Membership Record</h2>
    <table>
        <tr>
            <td class="label">Name</td>
            <td>{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</td>
        </tr>
        <tr>
            <td class="label">Username</td>
            <td>{{ $user->username }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <td class="label">Member Since</td>
            <td>{{ $user->created_at->format('F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Contact No.</td>
            <td>{{ $otherinfo->contact_no ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Present Address</td>
            <td>{{ $otherinfo->present_address ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Civil Status</td>
            <td>{{ $otherinfo->civil_status ?? '—' }}</td>
        </tr>
    </table>

    <h2>Savings</h2>
    <table>
        <tr>
            <td class="label">Balance</td>
            <td>₱{{ number_format($savingsAccount->total_amount ?? 0, 2) }}</td>
        </tr>
    </table>

    <h2>Share Capital</h2>
    <table>
        <tr>
            <td class="label">Balance</td>
            <td>₱{{ number_format($shareCapitalAccount->total_amount ?? 0, 2) }}</td>
        </tr>
    </table>

    <h2>Loans</h2>
    <table>
        <tr>
            <td class="label">Type</td>
            <td class="label">Amount</td>
            <td class="label">Status</td>
        </tr>
        @foreach($loans as $loan)
            <tr>
                <td>{{ $loan->lending_type }}</td>
                <td>₱{{ number_format($loan->lending_amount, 2) }}</td>
                <td>{{ $loan->status }}</td>
            </tr>
        @endforeach
    </table>
</body>

</html>