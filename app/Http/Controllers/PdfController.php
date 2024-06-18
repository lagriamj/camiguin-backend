<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationOrder;
use App\Models\DestinationTourTypePrices;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class PdfController extends Controller
{
    public function generateAndDownloadPDF(Request $request)
    {
        $request->validate([
            'start_date' => 'required',
            'end_date' => 'required',
            'auth' => 'required|string',
            'destination_id' => 'required|exists:destinations,id'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $auth_check = (new ActionAuthorizationController)->checkAuth($auth);

        if ($auth_check) {
            $report = $this->generateCollectionDestinationReport($request->destination_id, $request);

            $customPaper = array(0, 0, 500, 350);
            $pdf = \PDF::loadView('collection_report', compact('report'))->setPaper('a4', 'landscape');

            // Store the PDF in storage
            $filename = 'collection_destination_report_' . time() . '.pdf';
            $path = "reports/" . $filename;
            Storage::disk('public')->put($path, $pdf->output());

            $storagePath = 'public/' . $filename;

            $pdf->download($filename);

            return response(['message' => 'PDF Generated', 'path' => $path], 200);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function generateCollectionDestinationReport($destinationId, Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $destination = Destination::findOrFail($destinationId);

        $orders = DestinationOrder::whereHas('destinationOrderItems.destination', function ($query) use ($destinationId) {
            $query->where('id', $destinationId);
        })
            ->when($startDate && $endDate,function($q) use($startDate,$endDate){
                $q->whereDate('created_at', '>=', Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay())
                ->whereDate('created_at', '<=', Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay());
            })
            ->with('destinationOrderItems.tourTypePrice')
            ->orderByDesc('id')
            ->get();

        $report = collect();


        $totalCollectionWithoutOR = 0;
        $totalCollectionWithOR = 0;


        foreach ($orders as $order) {
            $totalAmount = $order->destinationOrderItems->sum(function ($item) {
                return $item->tourTypePrice->price;
            });

            if (!empty($order->or_number)) {
                // If the transaction number exists, consider it as an OR order
                $totalCollectionWithOR += $totalAmount;
            } else {
                $totalCollectionWithoutOR += $totalAmount;
            }
        }

        // Append total collection data to the report
        $report->push([
            'destination_name' => $destination->name,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'total_collection_without_or' => $totalCollectionWithoutOR,
            'total_collection_with_or' => $totalCollectionWithOR,
            'overall_collection' => $totalCollectionWithoutOR + $totalCollectionWithOR
        ]);

        // Iterate over orders to append order details
        foreach ($orders as $order) {
            $inviduals_id = $order->individuals_id;
            $individuals = DB::connection('second_database')->select('select * from individuals where id = ' . $inviduals_id);
            $individuals_name = $individuals[0]->first_name . ' ' . $individuals[0]->last_name;

            $individuals_qr_code = $individuals[0]->qr_code;

            // $tourist_count = $order->destinationOrderItems->whereHas('')
            //     ->whereIn('destination_tour_type_price_id', [1, 2])
            //     ->count();

            $tourist_count = $order->destinationOrderItems()->whereHas('tourTypePrice', function ($query) {
                $query->whereHas('touristType', function ($query) {
                    $query->whereIn('id', [2]);
                });
            })->count();

            $local_count  = $order->destinationOrderItems()->whereHas('tourTypePrice', function ($query) {
                $query->whereHas('touristType', function ($query) {
                    $query->whereIn('id', [1]);
                });
            })->count();
            
            $special  = $order->destinationOrderItems()->whereHas('tourTypePrice', function ($query) {
                $query->whereHas('touristType', function ($query) {
                    $query->whereIn('id', [3]);
                });
            })->count();

            $totalAmount = $order->destinationOrderItems->sum(function ($item) {
                return $item->tourTypePrice->price;
            });

            $report->push([
                'book_date' => $order->created_at,
                'qr_code'   => $individuals_qr_code,
                'name'      => $individuals_name,
                'tourist'   => $tourist_count,
                'local'     => $local_count,
                'special'   => $special,
                'tour_date' => $order->departure_time,
                'type'      => $order->order_type,
                'payment'   => $order->payment_method,
                'or_num'    => $order->or_number,
                'amount'    => $totalAmount
            ]);
        }

        return $report;
    }
}
