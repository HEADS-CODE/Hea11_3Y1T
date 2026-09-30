<?php

namespace App\Controllers;

use App\Models\CustomerAccountModel;

class Accounts extends BaseController
{
    protected CustomerAccountModel $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerAccountModel();
    }

    protected function requireLogin()
    {
        if (! session()->has('user_id')) {
            return redirect()->to(base_url('login'))->with('error', 'Please log in to view customer accounts.');
        }
        return null;
    }

    public function index()
    {
        if ($redirect = $this->requireLogin()) return $redirect;

        $keyword = $this->request->getGet('search');
        $status = $this->request->getGet('status');
        $type = $this->request->getGet('type');
        $perPage = 10;

        if ($keyword) $accounts = $this->customerModel->searchAccounts($keyword, $perPage);
        elseif ($status) $accounts = $this->customerModel->getAccountsByStatus($status, $perPage);
        elseif ($type) $accounts = $this->customerModel->getAccountsByType($type, $perPage);
        else $accounts = $this->customerModel->getAccountsPaginated($perPage);

        return view('accounts/index', [
            'accounts' => $accounts,
            'pager' => $this->customerModel->pager,
            'total_accounts' => $this->customerModel->getTotalAccounts(),
            'active_accounts' => $this->customerModel->getCountByStatus('active'),
            'inactive_accounts' => $this->customerModel->getCountByStatus('inactive'),
            'suspended_accounts' => $this->customerModel->getCountByStatus('suspended'),
            'current_page' => $this->request->getGet('page') ?? 1,
            'search_keyword' => $keyword,
            'filter_status' => $status,
            'filter_type' => $type,
            'message' => session()->getFlashdata('message'),
        ]);
    }

    public function viewAccount($id)
    {
        if ($redirect = $this->requireLogin()) return $redirect;
        $account = $this->customerModel->find($id);
        if (! $account) return redirect()->to(base_url('accounts'))->with('error', 'Account not found.');
        return view('accounts/view_account', ['account' => $account]);
    }

    public function create()
    {
        if ($redirect = $this->requireLogin()) return $redirect;
        return view('accounts/form', [
            'title' => 'Add Customer Account',
            'formAction' => base_url('accounts/store'),
            'account' => [],
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function store()
    {
        if ($redirect = $this->requireLogin()) return $redirect;
        if (! $this->validateAccount()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->customerModel->insert($this->accountData());
        return redirect()->to(base_url('accounts'))->with('message', 'Customer account created successfully.');
    }

    public function edit($id)
    {
        if ($redirect = $this->requireLogin()) return $redirect;
        $account = $this->customerModel->find($id);
        if (! $account) return redirect()->to(base_url('accounts'))->with('error', 'Account not found.');
        return view('accounts/form', [
            'title' => 'Edit Customer Account',
            'formAction' => base_url('accounts/update/' . $id),
            'account' => $account,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function update($id)
    {
        if ($redirect = $this->requireLogin()) return $redirect;
        if (! $this->customerModel->find($id)) return redirect()->to(base_url('accounts'))->with('error', 'Account not found.');
        if (! $this->validateAccount($id)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->customerModel->update($id, $this->accountData());
        return redirect()->to(base_url('accounts'))->with('message', 'Customer account updated successfully.');
    }

    public function delete($id)
    {
        if ($redirect = $this->requireLogin()) return $redirect;
        if ($this->customerModel->find($id)) {
            $this->customerModel->delete($id);
            return redirect()->to(base_url('accounts'))->with('message', 'Customer account deleted successfully.');
        }
        return redirect()->to(base_url('accounts'))->with('error', 'Account not found.');
    }

    protected function validateAccount(?int $id = null): bool
    {
        $unique = $id ? ',id,' . $id : '';
        return $this->validate([
            'account_number' => 'required|max_length[50]|is_unique[customer_accounts.account_number' . $unique . ']',
            'customer_name' => 'required|max_length[150]',
            'address' => 'required',
            'phone' => 'permit_empty|max_length[20]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'meter_number' => 'permit_empty|max_length[50]',
            'connection_type' => 'required|in_list[residential,commercial,industrial]',
            'status' => 'required|in_list[active,inactive,suspended]',
        ]);
    }

    protected function accountData(): array
    {
        return [
            'account_number' => trim((string) $this->request->getPost('account_number')),
            'customer_name' => trim((string) $this->request->getPost('customer_name')),
            'address' => trim((string) $this->request->getPost('address')),
            'phone' => trim((string) $this->request->getPost('phone')) ?: null,
            'email' => trim((string) $this->request->getPost('email')) ?: null,
            'meter_number' => trim((string) $this->request->getPost('meter_number')) ?: null,
            'connection_type' => $this->request->getPost('connection_type'),
            'status' => $this->request->getPost('status'),
        ];
    }
}
