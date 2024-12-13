<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\Request;
use Illuminate\Http\Response; 
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use App\Models\User;






class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('chirps.index', [
            'chirps' => Chirp::with('user')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);
 
        $request->user()->chirps()->create($validated);
 
        return redirect(route('chirps.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chirp $chirp): View
    {Gate::authorize('update', $chirp);
 
        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chirp $chirp): RedirectResponse
    {  Gate::authorize('update', $chirp);
 
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);
 
        $chirp->update($validated);
 
        return redirect(route('chirps.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chirp $chirp): RedirectResponse
    {
        Gate::authorize('delete', $chirp);
 
        $chirp->delete();
 
        return redirect(route('chirps.index'));
    }
    public function test_un_utilisateur_peut_creer_un_chirp()
    {
        // Simuler un utilisateur connecté
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);
        $reponse = $this->post('/chirps', [
        'content' => 'Mon premier chirp !'
    ]);
   
    
        $reponse->assertStatus(201);
        $this->assertDatabaseHas('chirps', [
        'content' => 'Mon premier chirp !',
        'user_id' => $utilisateur->id,
    ]);
    }
    public function test_un_chirp_ne_peut_pas_avoir_un_contenu_vide()
    {
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);
        $reponse = $this->post('/chirps', [
            'content' => ''
    ]);
        $reponse->assertSessionHasErrors(['contenu']);
    }
    public function test_un_chirp_ne_peut_pas_depasse_255_caracteres()
    {
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);
        $reponse = $this->post('/chirps', [
        'content' => str_repeat('a', 256)
    ]);
        $reponse->assertSessionHasErrors(['contenu']);
    }
    public function test_les_chirps_sont_affiches_sur_la_page_d_accueil()
    {
        $chirps = Chirp::factory()->count(3)->create();
        $reponse = $this->get('/');
        foreach ($chirps as $chirp) {
        $reponse->assertSee($chirp->contenu);
        }
    }
    public function test_un_utilisateur_peut_modifier_son_chirp()
    {
        $utilisateur = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);
        $this->actingAs($utilisateur);
        $reponse = $this->put("/chirps/{$chirp->id}", [
        'content' => 'Chirp modifié'
        ]); 
        $reponse->assertStatus(200);
        // Vérifie si le chirp existe dans la base de donnée.
        $this->assertDatabaseHas('chirps', [
        'id' => $chirp->id,
        'content' => 'Chirp modifié',
        ]);
        }
}
