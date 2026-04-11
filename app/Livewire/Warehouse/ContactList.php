<?php

namespace App\Livewire\Warehouse;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class ContactList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = ''; // all, customer, supplier, both
    public $showModal = false;
    public $isEdit = false;
    public $contactId;

    // Form fields
    public $name;
    public $address;
    public $phone;
    public $contact_person;
    public $email;
    public $type = 'supplier';
    public $status = 'active';

    protected $queryString = ['search', 'filterType'];

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'type' => 'required|in:customer,supplier,both',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['name', 'address', 'phone', 'contact_person', 'email', 'type', 'status', 'contactId']);
        
        if ($id) {
            $this->isEdit = true;
            $this->contactId = $id;
            $contact = Supplier::findOrFail($id);
            $this->name = $contact->name;
            $this->address = $contact->address;
            $this->phone = $contact->phone;
            $this->contact_person = $contact->contact_person;
            $this->email = $contact->email;
            $this->type = $contact->type;
            $this->status = $contact->status;
        } else {
            $this->isEdit = false;
            $this->type = 'supplier';
            $this->status = 'active';
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEdit) {
            $contact = Supplier::find($this->contactId);
            $contact->update([
                'name' => $this->name,
                'address' => $this->address,
                'phone' => $this->phone,
                'contact_person' => $this->contact_person,
                'email' => $this->email,
                'type' => $this->type,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Cập nhật thông tin thành công.');
        } else {
            Supplier::create([
                'name' => $this->name,
                'address' => $this->address,
                'phone' => $this->phone,
                'contact_person' => $this->contact_person,
                'email' => $this->email,
                'type' => $this->type,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Thêm đối tác mới thành công.');
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        $contact = Supplier::findOrFail($id);
        $contact->delete();
        session()->flash('message', 'Đã xoá đối tác.');
    }

    public function render()
    {
        $contacts = Supplier::query();

        // Chỉ áp dụng filter tìm kiếm nếu có giá trị search
        if (!empty($this->search)) {
            $contacts->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterType) {
            $contacts->where('type', $this->filterType);
        }

        $contacts = $contacts->latest()->paginate(15);

        return view('livewire.warehouse.contact-list', [
            'contacts' => $contacts
        ]);
    }
}
