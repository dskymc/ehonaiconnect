<?php
// Coded by DskyMC

namespace App\Controllers;

use App\Models\FaqModel;
use CodeIgniter\HTTP\RedirectResponse;

class FaqController extends BaseController
{
    protected $helpers = ['form', 'url'];

    protected function ensureAdmin(): ?RedirectResponse
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/faq')->with('error', 'Hanya Admin yang dapat mengelola FAQ.');
        }

        return null;
    }

    public function index(): string
    {
        /** @var FaqModel $faqModel */
        $faqModel = model(FaqModel::class);
        $faqs     = $faqModel->orderBy('id', 'ASC')->findAll();

        return view('faq/index', [
            'faqs'    => $faqs,
            'isAdmin' => session()->get('role') === 'admin',
        ]);
    }

    public function store(): RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $rules = [
            'pertanyaan' => 'required|max_length[255]',
            'jawaban'    => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/faq')->withInput()->with(
                'error',
                implode(' ', $this->validator->getErrors())
            );
        }

        /** @var FaqModel $faqModel */
        $faqModel = model(FaqModel::class);
        $inserted = $faqModel->insert([
            'pertanyaan' => (string) $this->request->getPost('pertanyaan'),
            'jawaban'    => (string) $this->request->getPost('jawaban'),
        ]);

        if ($inserted === false) {
            return redirect()->to('/faq')->withInput()->with('error', implode(' ', $faqModel->errors()));
        }

        return redirect()->to('/faq')->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function update(int $id): RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $rules = [
            'pertanyaan' => 'required|max_length[255]',
            'jawaban'    => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/faq')->withInput()->with(
                'error',
                implode(' ', $this->validator->getErrors())
            );
        }

        /** @var FaqModel $faqModel */
        $faqModel = model(FaqModel::class);
        $faq      = $faqModel->find($id);

        if ($faq === null) {
            return redirect()->to('/faq')->with('error', 'FAQ tidak ditemukan.');
        }

        $faqModel->update($id, [
            'pertanyaan' => (string) $this->request->getPost('pertanyaan'),
            'jawaban'    => (string) $this->request->getPost('jawaban'),
        ]);

        return redirect()->to('/faq')->with('success', 'FAQ berhasil diperbarui.');
    }

    public function delete(int $id): RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        /** @var FaqModel $faqModel */
        $faqModel = model(FaqModel::class);
        $faq      = $faqModel->find($id);

        if ($faq === null) {
            return redirect()->to('/faq')->with('error', 'FAQ tidak ditemukan.');
        }

        $faqModel->delete($id);

        return redirect()->to('/faq')->with('success', 'FAQ berhasil dihapus.');
    }
}
