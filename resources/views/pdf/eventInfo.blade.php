<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    h2, h3{
        display: inline;
    }
    table, th, td {
      border:1px solid black;
      text-align: center;
    }
</style>
<h2>
    Event Name: {{ $name }}
    <span style="position: absolute; right: 10px; top: 15px; font-size:9pt;">
        <a href="{{ env('APP_URL') }}">www.eventssmarter.com</a>
    </span>
</h2>
<hr>
<h3>
    Date:
</h3>
<span>
    From {{ $start_date }} to {{ $end_date }}
</span>
<br>
<h3>
    Stripe Account ID:
</h3>
<span>
    {{ $account_id }}
</span>
<br>
<h3>
    Event URL:
</h3>
<span>
    {{ $url }}
</span>
<br>
<h3>
    Total Processing Volume:
</h3>
<h3>
    {{ $volume }}
</h3>
<br>
<br>
<h3>
    EventsSmarter Reserved Amount:
</h3>
<h2 style="color: green">
    {{ $reserved_volume }}
</h2>
<br>
<table>
    <tr>
        <th>
            No
        </th>
        <th>
            Payment Intent ID
        </th>
        <th>
            Name
        </th>
        <th>
            Email
        </th>
        <th>
            Amount
        </th>
        <th>
            EventsSmarter Reserved Amount
        </th>
        <th>
            Date
        </th>
    </tr>
    @foreach($vouchers as $key => $item)
    <tr>
        <td>
        {{ $key + 1 }} 
        </td>
        <td>
            {{ $item->upgrade_charge_id }}
        </td>
        <td>
            {{ $item->user->name }}        
        </td>
        <td>
            {{ $item->user->email }}
        </td>
        <td>
            {{ 
                $currency.' '.number_format( (($item->each_sum_amount + 10 ) / (1 - $app_fee_percentage * .01) / 0.94 + 30 ) / .971 / 100, 2) 
            }}
        </td>
        <td style="color: green">
            {{ 
                $currency.' '.number_format( (($item->each_sum_amount + 10 ) / (1 - $app_fee_percentage * .01) / 0.94 - $item->each_sum_amount )/ 100, 2) 
            }}
        </td>
        <td>
            {{ $item->created_at }}
        </td>
    </tr>
    @endforeach
</table>