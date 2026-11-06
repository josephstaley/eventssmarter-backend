<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Helpers;
use Illuminate\Http\Request;
use DB;

class SalesController extends Controller
{
    public function show(Event $event, Request $request)
    {
        if (auth()->user()->admin) {
            $event = Event::where('id', $event->id)->firstOrFail();
        } else
            $event = auth()->user()->events()->where('id', $event->id)->firstOrFail();

        $sales = with(clone $event->sales()->query);
        $search = $request['search'];
        if (isset($search) && $search) {
            $sales = $sales->where(function ($query) use ($search) {
                $query->where('users.name', 'LIKE', '%' . $search . '%')->orWhere('users.email', 'LIKE', '%' . $search . '%');
                if (auth()->user()->admin) {
                    $query->orWhere('charge_id', 'LIKE', '%' . $search . '%');
                }
            });
        }
        $sales = $sales->paginate();

        $sales->setPath('');

        $promo_codes = with(clone $event->sales()->query)->select(DB::raw('count(*) as count'), DB::raw('sum(voucher_bundles.affiliate_amount) as sum_amount'), 'promo_code_org_id', 'promo_code', 'promo_codes.affiliate_email')->groupBy('promo_code_org_id')->get();
        $currency = Helpers::getCurrency($event->organization->currency);

        return view('sales.show', compact('event', 'sales', 'currency', 'promo_codes'))->with(['search' => $request['search']]);
    }

    public function exportcsv(Event $event)
    {
        $fileName = $event->name . '_ticket_sales.csv';

        $sales = $event->sales()->query->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        $currency = $event->organization->currency;
        $zero_decimal = Helpers::getCurrency($currency)['zero_decimal'];
        $columns = array('No', 'Name', 'Email', 'Phone', 'Ticket Name', "Price ($currency)", 'Promo Code', 'AdsId', 'Affiliate Amount', 'Address', 'Employer', 'Occupation', 'Prompt');
        $callback = function () use ($sales, $columns, $currency, $zero_decimal) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($sales as $key => $sale) {
                $row['No'] = $key + 1;
                $row['Name'] = $sale->name;
                $row['Email'] = $sale->email;
                $row['Phone'] = $sale->phone_number;
                $row['Ticket Name'] = $sale->detail_text;
                $row["Price ($currency)"] = $zero_decimal ? $sale->detail_amount : $sale->detail_amount / 100;
                $row['Promo Code'] = $sale->promo_code;
                $row['AdsId'] = $sale->adsid;
                $row['Affiliate Amount'] = $sale->affiliate_amount;
                $row['Address'] = $sale->address;
                $row['Employer'] = $sale->employer;
                $row['Occupation'] = $sale->occupation;
                $prompt = '';
                if ($sale->prompt_answer_json) {
                    $prompt_answers = json_decode($sale->prompt_answer_json);
                    foreach ($prompt_answers as $answer) {
                        $prompt .= $answer->title . ': ' . $answer->answer . ', ';
                    }
                    if ($prompt !== '') {
                        $prompt = substr($prompt, 0, -2);
                    }
                }
                $row['Prompt'] = $prompt;
                fputcsv($file, array($row['No'], $row['Name'], $row['Email'], $row['Phone'], $row['Ticket Name'], $row["Price ($currency)"], $row['Promo Code'], $row['AdsId'], $row['Affiliate Amount'], $row['Address'], $row['Employer'], $row['Occupation'], $row['Prompt']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
