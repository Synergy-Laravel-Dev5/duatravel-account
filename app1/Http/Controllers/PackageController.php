<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\Company;
use App\Models\Giveaway;
use App\Models\Hotel;
use App\Models\Package;
use App\Models\Train;
use App\Models\TrainingSession;
use App\Models\TravelRoute;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    protected $itineraryImagePath = 'assets/images/packages/itinerary';

    public function index()
    {
        $packages = Package::latest()->paginate(20);
        $trashCount = Package::onlyTrashed()->count();

        return view('package.index', compact('packages', 'trashCount'));
    }
    public function create()
    {
        $package = new Package();
        $giveaways = Giveaway::all();
        $companies = Company::all();
        $trainingSessions = TrainingSession::all();
        $hotels = Hotel::where('status', 'active')->get();
        $airlines = Airline::where('status', 'active')->get();
        $trainsList = Train::where('status', 'active')->get();
        $travelRoutes = TravelRoute::with('vehicle')->where('status', 'active')->get();

        return view('package.create', compact('package', 'giveaways', 'companies', 'trainingSessions', 'hotels', 'airlines', 'trainsList', 'travelRoutes'));
    }
    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $data) {
            $package = new Package();
            $package->fill($data['package']);
            $package->save();

            $this->saveRelations($request, $package, $data);
        });

        return redirect()->route('package.index')->with('success', 'Package created successfully.');
    }
    public function edit($id)
    {
        $package = Package::with([
            'accommodations',
            'itinerary',
            'terms',
            'maktabAddress',
            'transports',
            'transportFlights',
            'transportTrains',
            'giveaways',
            'trainingSessions',
        ])->findOrFail($id);

        $giveaways = Giveaway::all();
        $companies = Company::all();
        $trainingSessions = TrainingSession::all();
        $hotels = Hotel::where('status', 'active')->get();
        $airlines = Airline::where('status', 'active')->get();
        $trainsList = Train::where('status', 'active')->get();
        $travelRoutes = TravelRoute::with('vehicle')->where('status', 'active')->get();

        return view('package.edit', compact('package', 'giveaways', 'companies', 'trainingSessions', 'hotels', 'airlines', 'trainsList', 'travelRoutes'));
    }
    public function show($id)
    {
        $package = Package::with([
            'company',
            'accommodations.hotel',
            'itinerary',
            'terms',
            'maktabAddress',
            'transports',
            'transportFlights',
            'transportTrains',
            'trainingSessions',
            'giveaways',
        ])->findOrFail($id);

        return view('package.show', compact('package'));
    }
    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $package, $data) {
            $package->fill($data['package']);
            $package->save();

            $package->accommodations()->delete();
            $package->transports()->delete();
            $package->transportFlights()->delete();
            $package->transportTrains()->delete();

            $this->saveRelations($request, $package, $data);
        });

        return redirect()->route('package.index')->with('success', 'Package updated successfully.');
    }
    public function destroy($id)
    {
        Package::findOrFail($id)->delete();

        return back()->with('success', 'Package moved to trash.');
    }
    public function trash()
    {
        $packages = Package::onlyTrashed()->latest()->paginate(20);

        return view('package.trash', compact('packages'));
    }
    public function restore($id)
    {
        Package::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('package.trash')->with('success', 'Package restored successfully.');
    }
    private function storeFile($file, string $folder): string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($folder), $filename);

        return $filename;
    }
    private function deleteFile(?string $filename, string $folder): void
    {
        if ($filename && file_exists(public_path($folder . '/' . $filename))) {
            unlink(public_path($folder . '/' . $filename));
        }
    }
    private function validated(Request $request): array
    {

      $amountFields = [
        'package_amount',
        'ticket_amount',
        'extra_facilities_amount',
    ];

    foreach ($amountFields as $field) {
        if ($request->has($field)) {
            $request[$field] = str_replace(',', '', $request[$field]);
        }
    }
        $package = $request->validate([
            'year' => 'nullable|string|max:20',
            // 'company_id' => 'nullable|integer|exists:companies,id',
            'package_number' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:150',
            'zone' => 'nullable|string|max:150',
            'name' => 'nullable|string|max:200',
            'code' => 'nullable|string|max:100',
            'days' => 'nullable|integer',

            // ================= Pakage Type =================
            'package_type' => 'nullable|string',
            'package_amount' => 'nullable|numeric',
            'food_package' => 'nullable|string',
            'food_included' => 'nullable|in:yes,no',
            'ticket_amount' => 'nullable|numeric',
            'extra_facilities' => 'nullable|in:yes,no',
            'extra_facilities_amount' => 'nullable|numeric',
            'other_extra_facilities' => 'nullable|string',
            'qurbani_included' => 'nullable|in:yes,no',
            'ticket_included' => 'nullable|in:yes,no',
            'place_of_departure' => 'nullable|string',
            'flight_type' => 'nullable|string',
            'flight_class' => 'nullable|string',
            // =============================================

            'maktab' => 'nullable|string|max:150',
            'maktab_number' => 'nullable|string|max:100',
            'medina_arrival' => 'nullable|in:before_hajj,after_hajj',
            'hajj_duration' => 'nullable|in:short,long',

            'room_type' => 'nullable|string|max:100',
            'azizia_room_type' => 'nullable|string|max:100',
            'makkah_type' => 'nullable|string|max:100',
            'medinah_type' => 'nullable|string|max:100',
            'azizia_type' => 'nullable|string|max:100',
            'mina_type' => 'nullable|string|max:100',
            'giveaway_note' => 'nullable|string',
        ]);

        $accommodations = $request->validate([
            'accommodations' => 'nullable|array',
            'accommodations.*.place' => 'nullable|string|max:150',
            'accommodations.*.accommodation_type' => 'nullable|string|max:150',
            'accommodations.*.saudi_star_rating' => 'nullable|string|max:50',
            'accommodations.*.hotel_id' => 'nullable|integer|exists:hotels,id',
            'accommodations.*.distance' => 'nullable|integer',
            'accommodations.*.check_in' => 'nullable|date',
            'accommodations.*.check_out' => 'nullable|date',
            'accommodations.*.food_package' => 'nullable|string|max:100',
            'accommodations.*.actual_check_in_time' => 'nullable|date',
            'accommodations.*.actual_check_out_time' => 'nullable|date',
            'accommodations.*.days' => 'nullable|integer',
            'accommodations.*.nights' => 'nullable|integer',
            'accommodations.*.makkah_ziarat' => 'nullable|in:yes,no',
            'accommodations.*.madinah_ziarat' => 'nullable|in:yes,no',
            'accommodations.*.distribution' => 'nullable|string|max:150',
            'accommodations.*.camp' => 'nullable|string|max:150',
            'accommodations.*.arafat' => 'nullable|string|max:150',
            'accommodations.*.shuttle' => 'nullable|string|max:150',
            'accommodations.*.bedding' => 'nullable|string|max:150',
            'accommodations.*.sharing' => 'nullable|string|max:150',
            'accommodations.*.sharing_type' => 'nullable|string|max:150',
            'accommodations.*.note' => 'nullable|string',
        ])['accommodations'] ?? [];

        $itinerary = $request->validate([
            'itinerary_description' => 'nullable|string',
        ]);

        $terms = $request->validate([
            'terms_content' => 'nullable|string',
        ]);

        $transports = $request->validate([
            'transports' => 'nullable|array',
            'transports.*.route' => 'nullable|string|max:150',
            'transports.*.arrival' => 'nullable|string|max:100',
            'transports.*.departure' => 'nullable|string|max:100',
            'transports.*.arrival_date' => 'nullable|date',
            'transports.*.arrival_time' => 'nullable',
            'transports.*.type' => 'nullable|string|max:100',
            'transports.*.vehicle' => 'nullable|string|max:100',
        ])['transports'] ?? [];

        $flights = $request->validate([
            'flights' => 'nullable|array',
            'flights.*.airline' => 'nullable|string|max:150',
            'flights.*.flight_no' => 'nullable|string|max:100',
            'flights.*.flight_class' => 'nullable|string|max:100',
            'flights.*.origin' => 'nullable|string|max:150',
            'flights.*.destination' => 'nullable|string|max:150',
            'flights.*.departure_date' => 'nullable|date',
            'flights.*.departure_time' => 'nullable|date_format:H:i',
            'flights.*.arrival_date' => 'nullable|date',
            'flights.*.arrival_time' => 'nullable|date_format:H:i',
            'flights.*.pnr_no' => 'nullable|string|max:100',
            'flights.*.ticket_amount' => 'nullable|numeric',
            'flights.*.is_preferred' => 'nullable|boolean',
        ])['flights'] ?? [];

        $trains = $request->validate([
            'trains' => 'nullable|array',
            'trains.*.railway' => 'nullable|string|max:150',
            'trains.*.train_no' => 'nullable|string|max:100',
            'trains.*.train_class' => 'nullable|string|max:100',
            'trains.*.origin' => 'nullable|string|max:150',
            'trains.*.destination' => 'nullable|string|max:150',
            'trains.*.departure_date' => 'nullable|date',
            'trains.*.departure_time' => 'nullable|date_format:H:i',
            'trains.*.arrival_date' => 'nullable|date',
            'trains.*.arrival_time' => 'nullable|date_format:H:i',
            'trains.*.pnr_no' => 'nullable|string|max:100',
            'trains.*.ticket_amount' => 'nullable|numeric',
        ])['trains'] ?? [];

        $maktabAddress = $request->validate([
            'maktab_address' => 'nullable|string|max:200',
            'office_address' => 'nullable|string|max:200',
        ]);

        $giveaways = $request->validate([
            'giveaways' => 'nullable|array',
            'giveaways.*' => 'integer|exists:giveaways,id',
        ])['giveaways'] ?? [];

        $trainingSessions = $request->validate([
            'training_sessions' => 'nullable|array',
            'training_sessions.*' => 'integer|exists:training_sessions,id',
        ])['training_sessions'] ?? [];

        return compact(
            'package',
            'accommodations',
            'itinerary',
            'terms',
            'transports',
            'flights',
            'trains',
            'maktabAddress',
            'giveaways',
            'trainingSessions'
        );
    }
    private function saveRelations(Request $request, Package $package, array $data): void
    {
        foreach ($data['accommodations'] as $row) {
            $package->accommodations()->create($row);
        }

        $existingItinerary = $package->itinerary;
        $itineraryData = ['description' => $data['itinerary']['itinerary_description'] ?? null];

        $imageFields = [
            'mina_image',
            'arafat_image',
            'muzdalifah_image',
            'makkah_mina_rami_day_one_image',
            'mina_rami_day_two_image',
            'mina_makkah_rami_day_three_image',
        ];

        foreach ($imageFields as $imageField) {
            if ($request->hasFile($imageField)) {
                if ($existingItinerary && $existingItinerary->$imageField) {
                    $this->deleteFile($existingItinerary->$imageField, $this->itineraryImagePath);
                }
                $itineraryData[$imageField] = $this->storeFile($request->file($imageField), $this->itineraryImagePath);
            }
        }

        $package->itinerary()->updateOrCreate(['package_id' => $package->id], $itineraryData);

        // Terms & Condition
        $package->terms()->updateOrCreate(
            ['package_id' => $package->id],
            ['content' => $data['terms']['terms_content'] ?? null]
        );

        // Transport (repeatable)
        foreach ($data['transports'] as $row) {
            $package->transports()->create($row);
        }

        // Flights (repeatable)
        foreach ($data['flights'] as $row) {
            $row['is_preferred'] = filter_var($row['is_preferred'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $package->transportFlights()->create($row);
        }

        // Trains (repeatable)
        foreach ($data['trains'] as $row) {
            $package->transportTrains()->create($row);
        }

        $package->maktabAddress()->updateOrCreate(
            ['package_id' => $package->id],
            $data['maktabAddress']
        );

        $package->giveaways()->sync($data['giveaways']);

        $package->trainingSessions()->sync($data['trainingSessions']);
    }
}
