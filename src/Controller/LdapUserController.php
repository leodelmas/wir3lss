<?php

namespace App\Controller;

use App\Dto\LdapUser;
use App\Form\LdapUserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Ldap\Ldap;

#[Route('/users')]
class LdapUserController extends AbstractController
{
    public function __construct(
        private string $ldapServer,
        private string $ldapPortalDn,
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
        $query = $ldap->query($this->ldapPortalDn, '(&(objectclass=person))');
        $results = $query->execute()->toArray();

        $users = [];
        /**  @var Entry $entry */
        foreach ($results as $entry) {
            $users[] = LdapUser::create($entry->getAttributes());
        }

        return $this->render('ldap_user/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/new', name: 'ldap_user.new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $ldapUserDto = new LdapUser();
        $form = $this->createForm(LdapUserType::class, $ldapUserDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ldap = Ldap::create('ext_ldap', [
                'host' => $this->ldapServer
            ]);
            $ldap->bind($this->ldapSearchDn, $this->ldapSearchPassword);

            $entry = new Entry('CN=' . $ldapUserDto->cn . ',' . $this->ldapPortalDn, [
                'objectClass' => ['top', 'person', 'organizationalPerson', 'user'],
                'mail' => [$ldapUserDto->email],
                'telephoneNumber' => [$ldapUserDto->phone],
                'displayName' => [$ldapUserDto->displayedName]
            ]);
            $entryManager = $ldap->getEntryManager();
            $entryManager->add($entry);

            return $this->redirectToRoute('ldap_user.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ldap_user/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{cn}/edit', name: 'ldap_user.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $cn): Response
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

    #[Route('/{cn}', name: 'ldap_user.delete', methods: ['POST'])]
    public function delete(Request $request, string $cn): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cn, $request->request->get('_token'))) {
            $ldap = Ldap::create('ext_ldap', [
                'host' => $this->ldapServer
            ]);
            $ldap->bind($this->ldapSearchDn, $this->ldapSearchPassword);
            $entryManager = $ldap->getEntryManager();
            $entryManager->remove(new Entry('CN=' . $cn . ',' . $this->ldapPortalDn));
        }
        return $this->redirectToRoute('ldap_user.index', [], Response::HTTP_SEE_OTHER);
    }
}
