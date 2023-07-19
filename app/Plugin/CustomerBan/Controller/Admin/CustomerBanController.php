<?php

namespace Plugin\CustomerBan\Controller\Admin;

use Eccube\Common\Constant;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\Master\PageMaxRepository;
use Knp\Component\Pager\Paginator;
use Eccube\Controller\AbstractController;
use Eccube\Form\Type\Admin\SearchCustomerType;
use Eccube\Util\FormUtil;
use Plugin\CustomerBan\Entity\CustomerBan;
use Plugin\CustomerBan\Repository\CustomerBanRepository;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class CustomerBanController extends AbstractController
{
    /**
     * @var PageMaxRepository
     */
    protected $pageMaxRepository;
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CustomerBanRepository
     */
    protected $customerBanRepository;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(
        PageMaxRepository $pageMaxRepository,
        CustomerRepository $customerRepository,
        CustomerBanRepository $customerBanRepository,
        RouterInterface $router
    )
    {
        $this->pageMaxRepository = $pageMaxRepository;
        $this->customerRepository = $customerRepository;
        $this->customerBanRepository = $customerBanRepository;
        $this->router = $router;
    }

    /**
     * @Route("/%eccube_admin_route%/customer_ban/", name="customer_ban_admin_customer_ban")
     * @Route("/%eccube_admin_route%/customer_ban/{page_no}", requirements={"page_no" = "\d+"}, name="customer_ban_admin_customer_ban_page")
     * @Template("@CustomerBan/admin/index.twig")
     *
     * @param Request $request
     * @param int|null $page_no
     * @param Paginator $paginator
     * @return array
     */
    public function index(Request $request, $page_no = null, Paginator $paginator)
    {
        $session = $this->session;
        $builder = $this->formFactory->createBuilder(SearchCustomerType::class);

        $searchForm = $builder->getForm();

        $pageMaxis = $this->pageMaxRepository->findAll();
        $pageCount = $session->get('customer_ban.admin.search.page_count', $this->eccubeConfig['eccube_default_page_count']);
        $pageCountParam = $request->get('page_count');
        if ($pageCountParam && is_numeric($pageCountParam)) {
            foreach ($pageMaxis as $pageMax) {
                if ($pageCountParam == $pageMax->getName()) {
                    $pageCount = $pageMax->getName();
                    $session->set('customer_ban.admin.search.page_count', $pageCount);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();
                $page_no = 1;

                $session->set('customer_ban.admin.search', FormUtil::getViewData($searchForm));
                $session->set('customer_ban.admin.search.page_no', $page_no);
            } else {
                return [
                    'searchForm' => $searchForm->createView(),
                    'pagination' => [],
                    'pageMaxis' => $pageMaxis,
                    'page_no' => $page_no,
                    'page_count' => $pageCount,
                    'has_errors' => true,
                ];
            }
        } else {
            if (null !== $page_no || $request->get('resume')) {
                if ($page_no) {
                    $session->set('customer_ban.admin.search.page_no', (int) $page_no);
                } else {
                    $page_no = $session->get('customer_ban.admin.search.page_no', 1);
                }
                $viewData = $session->get('customer_ban.admin.search', []);
            } else {
                $page_no = 1;
                $viewData = FormUtil::getViewData($searchForm);
                $session->set('customer_ban.admin.search', $viewData);
                $session->set('customer_ban.admin.search.page_no', $page_no);
            }
            $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
        }

        $qb = $this->customerBanRepository->getQueryBuilderBySearchData($searchData);

        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $pageCount
        );

        return [
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $pageCount,
            'has_errors' => false,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/customer_ban/ban/{id}", name="customer_ban_admin_ban")
     *
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ban(Request $request, $id)
    {
        $Customer = $this->customerRepository->find($id);
        if (!$Customer) {
            throw new NotFoundHttpException();
        }

        $page_no = intval($this->session->get('eccube.admin.customer.search.page_no'));
        $page_no = $page_no ? $page_no : Constant::ENABLED;

        $CustomerBan = $this->customerBanRepository->findBy(['Customer' => $Customer]);
        if (!empty($CustomerBan)) {
            $this->deleteMessage();

            return $this->redirect($this->generateUrl('admin_customer_page',
                    ['page_no' => $page_no]).'?resume='.Constant::ENABLED);
        }
        log_info('不正顧客登録開始', [$Customer->getId()]);
        $CustomerBan = new CustomerBan();
        $CustomerBan->setCustomer($Customer);
        try {
            $this->entityManager->persist($CustomerBan);
            $this->entityManager->flush($CustomerBan);
            $this->addSuccess('customer_ban.admin.ban.success', 'admin');
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $this->addError('customer_ban.admin.ban.error', 'admin');
        }
        log_info('不正顧客登録完了', [$Customer->getId()]);

        $referrer = $this->getRefererRoute($request);
        if ($referrer === 'admin_customer_page') {
            $page_no = intval($this->session->get('eccube.admin.customer.search.page_no'));
        } else {
            $page_no = intval($this->session->get('customer_ban.admin.search.page_no'));
        }
        $page_no = $page_no ? $page_no : Constant::ENABLED;

        return $this->redirect($this->generateUrl($referrer,
                ['page_no' => $page_no]).'?resume='.Constant::ENABLED);
    }

    /**
     * @Route("/%eccube_admin_route%/customer_ban/unban/{id}", name="customer_ban_admin_unban")
     *
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unban(Request $request, $id)
    {
        $CustomerBan = $this->customerBanRepository->find($id);
        if (!$CustomerBan) {
            throw new NotFoundHttpException();
        }

        log_info('不正顧客解除開始', [$CustomerBan->getId()]);

        $this->entityManager->remove($CustomerBan);
        $this->entityManager->flush();
        $this->addSuccess('customer_ban.admin.unban.success', 'admin');

        log_info('不正顧客解除完了', [$CustomerBan->getId()]);

        $referrer = $this->getRefererRoute($request);
        if ($referrer === 'admin_customer_page') {
            $page_no = intval($this->session->get('eccube.admin.customer.search.page_no'));
        } else {
            $page_no = intval($this->session->get('customer_ban.admin.search.page_no'));
        }
        $page_no = $page_no ? $page_no : Constant::ENABLED;

        return $this->redirect($this->generateUrl($referrer,
                ['page_no' => $page_no]).'?resume='.Constant::ENABLED);
    }

    protected function getRefererRoute(Request $request)
    {
        //look for the referer route
        $referer = $request->headers->get('referer');
        $baseUrl = $request->getSchemeAndHttpHost();
        $lastPath = substr($referer, strpos($referer, $baseUrl) + strlen($baseUrl));
        if (strpos($lastPath, 'admin/customer_ban') !== false) {
            return 'customer_ban_admin_customer_ban_page';
        } else {
            return 'admin_customer_page';
        }
    }
}
