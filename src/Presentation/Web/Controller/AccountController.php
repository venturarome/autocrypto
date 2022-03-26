<?php

namespace App\Presentation\Web\Controller;

use App\Application\Service\Asset\GetAssetInfo;
use App\Application\Service\Asset\GetAssetInfoRequest;
use App\Infrastructure\Persistence\Doctrine\Repository\Account\AccountRepository;
use App\Infrastructure\Persistence\Doctrine\Repository\Account\BalanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/accounts', name: 'accounts', methods: ['GET'])]
    public function index(AccountRepository $accountRepo): Response
    {
        return $this->render('@Web/Account/index.html.twig', [
            'accounts' => $accountRepo->findAll(),
        ]);
    }

    #[Route('/account/{reference}', name: 'account', methods: ['GET'])]
    public function account(AccountRepository $accountRepo, BalanceRepository $balanceRepo, string $reference): Response
    {
        $account = $accountRepo->findByReference($reference);
        $balances = $account ? $balanceRepo->findOfAccount($account) : null;

        return $this->render('@Web/Account/account.html.twig', [
            'reference' => $reference,
            'account' => $account,
            'balances' => $balances,
        ]);
    }
}
