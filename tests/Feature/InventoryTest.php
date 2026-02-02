<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Medication;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class InventoryTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    \Spatie\Permission\Models\Role::create(['name' => 'patient']);
    \Spatie\Permission\Models\Role::create(['name' => 'system_admin']);
  }

  public function test_can_render_medication_index()
  {
    $user = User::factory()->create();
    $user->assignRole('system_admin');

    $this->actingAs($user)
      ->get(route('inventory.mex.index'))
      ->assertOk()
      ->assertSee('Medication Inventory');
  }

  public function test_can_create_medication()
  {
    $user = User::factory()->create();
    $user->assignRole('system_admin');

    $component = Volt::test('inventory.medication-form')
      ->set('name', 'Test Med')
      ->set('type', 'Tablet')
      ->set('min_threshold', 5)
      ->call('save');

    $component->assertHasNoErrors();

    $this->assertDatabaseHas('medications', [
      'meds_name' => 'Test Med',
      'meds_type' => 'Tablet',
      'stock_quantity' => 0,
    ]);
  }

  public function test_can_edit_medication()
  {
    $user = User::factory()->create();
    $user->assignRole('system_admin');
    $med = Medication::factory()->create([
      'meds_name' => 'Old Name',
      'min_threshold' => 10
    ]);

    $component = Volt::test('inventory.medication-form')
      ->call('setEditMed', $med->meds_id)
      ->set('name', 'New Name')
      ->call('save');

    $component->assertHasNoErrors();

    $this->assertDatabaseHas('medications', [
      'meds_id' => $med->meds_id,
      'meds_name' => 'New Name',
    ]);
  }

  public function test_can_adjust_stock_in()
  {
    $user = User::factory()->create();
    $user->assignRole('system_admin');
    $med = Medication::factory()->create([
      'stock_quantity' => 10
    ]);

    Volt::actingAs($user)
      ->test('inventory.stock-adjustment')
      ->call('setMed', $med->meds_id)
      ->set('quantity', 5)
      ->set('type', 'IN')
      ->set('reason', 'Purchase')
      ->call('adjust')
      ->assertHasNoErrors();

    $this->assertDatabaseHas('medications', [
      'meds_id' => $med->meds_id,
      'stock_quantity' => 15,
    ]);

    $this->assertDatabaseHas('stock_movements', [
      'meds_id' => $med->meds_id,
      'quantity' => 5,
      'type' => 'IN',
      'reason' => 'Purchase',
    ]);
  }

  public function test_can_adjust_stock_out()
  {
    $user = User::factory()->create();
    $user->assignRole('system_admin');
    $med = Medication::factory()->create([
      'stock_quantity' => 10
    ]);

    Volt::actingAs($user)
      ->test('inventory.stock-adjustment')
      ->call('setMed', $med->meds_id)
      ->set('quantity', 3)
      ->set('type', 'OUT')
      ->set('reason', 'Damage')
      ->call('adjust')
      ->assertHasNoErrors();

    $this->assertDatabaseHas('medications', [
      'meds_id' => $med->meds_id,
      'stock_quantity' => 7,
    ]);
  }
}
