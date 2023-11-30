<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{

    // Show all listings
    public function index()
    {
        return view('listings.index', [
            'listings' => Listing::latest()
                ->filter(request(['tag', 'search']))
                // ->get()
                ->paginate(6)
            // ->simplepaginate(2)
        ]);
    }

    //show single listing
    public function show(Listing $listing)
    {
        return view('listings.show', [
            'listing' => $listing
        ]);
    }

    //show Create Form
    public function create()
    {
        return view('Listings.create');
    }
    //store listing data
    public function store(Request $request)
    {
        // dd($request->file('logo')->store());
        $formFields = $request->validate([
            'title' => 'required',
            'company' => [
                'required',
                Rule::unique('listings', 'company')
            ],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);
        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')
                ->store('logos', 'public');
        }

        $formFields['user_id'] = auth()->id();
        Listing::create($formFields);

        // Session::flash('message','Listing ')
        return redirect('/')->with('message', 'Listing created sucessfully');
    }


    //show Edit Form
    public function edit(Listing $listing)
    {

        return view('listings.edit', ['listing' => $listing]);
    }

    //store listing data
    public function update(Request $request, Listing $listing)
    {

        //Make sure logged in user is owner
        if ($listing->user_is != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }
        $formFields = $request->validate([
            'title' => 'required',
            'company' => 'required',
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);
        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')
                ->store('logos', 'public');
        }
        $listing->update($formFields);

        // Session::flash('message','Listing ')
        return back()->with('message', 'Listing updated sucessfully');
    }




    // Delete Listing
    public function destroy(Listing $listing)
    {
        // Make sure logged in user is owner
        if ($listing->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action');
        }

        // if ($listing->logo && Storage::disk('public')->exists($listing->logo)) {
        //     Storage::disk('public')->delete($listing->logo);
        // }
        $listing->delete();
        return redirect('/')->with('message', 'Listing deleted successfully');
    }









    //Mnaage Listings

    public function manage()
    {
        // return view('listings.manage', ['listings' => auth()->user()->listings->get()]);
        return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);
    }
}
