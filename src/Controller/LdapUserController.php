<?php

namespace App\Controller;

use App\Form\LdapUserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Ldap\Ldap;

#[Route('/ldap')]
class LdapUserController extends AbstractController
{
    public function __construct(
        private string $ldapServer,
        private string $ldapBaseDn,
        private string $ldapSearchDn,
        private string $ldapSearchPassword
    )
    {
    }

    #[Route('/', name: 'ldap_user.index', methods: ['GET'])]
    public function index(): Response
    {
        $ldap = Ldap::create('ext_ldap', [
            'host' => $this->ldapServer
        ]);
        $ldap->bind($this->ldapSearchDn, $this->ldapSearchPassword);
        $query = $ldap->query($this->ldapBaseDn, '(&(objectclass=person))');
        $results = $query->execute()->toArray();

        dd($results);

        return $this->render('ldap_user/index.html.twig', [
        ]);
    }

    #[Route('/new', name: 'ldap_user.new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $form = $this->createForm(LdapUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: Save user by LDAP
            return $this->redirectToRoute('ldap_user.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ldap_user/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'ldap_user.show', methods: ['GET'])]
    public function show(string $userCn): Response
    {
        // TODO: Get user by LDAP
        return $this->render('ldap_user/show.html.twig');
    }

    #[Route('/{id}/edit', name: 'ldap_user.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $userCn): Response
    {
        $form = $this->createForm(LdapUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: Save user by LDAP
            return $this->redirectToRoute('ldap_user.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ldap_user/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'ldap_user.delete', methods: ['POST'])]
    public function delete(Request $request, string $userCn): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userCn, $request->request->get('_token'))) {
            // TODO: Delete user by LDAP
        }

        return $this->redirectToRoute('ldap_user.index', [], Response::HTTP_SEE_OTHER);
    }
}
