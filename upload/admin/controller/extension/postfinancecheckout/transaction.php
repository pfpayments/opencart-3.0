<?php
require_once modification(DIR_SYSTEM . 'library/postfinancecheckout/helper.php');

class ControllerExtensionPostFinanceCheckoutTransaction extends \PostFinanceCheckout\Controller\AbstractController {

	public function index(){
		$this->load->language('extension/payment/postfinancecheckout');
		
		$this->document->setTitle($this->language->get('heading_transaction_list'));
		
		$this->load->model('extension/postfinancecheckout/transaction');
		
		$this->getList();
	}

	protected function getList(){
		$filters = $this->getFilters();
		$breadcrumbs = $this->getBreadcrumbs();
		$transactions = $this->model_extension_postfinancecheckout_transaction->loadList($filters);
		$transactionCount = $this->model_extension_postfinancecheckout_transaction->countRows();
		
		$use_space_view = false;
		$space_view = null;
		foreach ($transactions as $transaction) {
			if (!$space_view) {
				$space_view = $transaction['space_view_id'];
			}
			else if ($space_view != $transaction['space_view_id']) {
				$use_space_view = true;
				break;
			}
		}
		
		$data = array_merge($this->loadLanguageVariables(), $this->getSortLinks($filters));
		$data['use_space_view'] = $use_space_view;
		$data['breadcrumbs'] = $breadcrumbs;
		$data['transactions'] = $transactions;
		$data['filters'] = $filters;
		$data['pagination'] = $this->getPagination($filters, $transactionCount)->render();
		$data['results'] = $this->getResultsText($transactionCount, $filters['page']);
		
		$filters['page'] = 1; // reset to page one on search. leave other filters.
		$data['filterAction'] = PostFinanceCheckoutVersionHelper::createUrl($this->url, 'extension/postfinancecheckout/transaction',
				$this->getQueryString($filters), true);
		
		$data['order_statuses'] = $this->model_extension_postfinancecheckout_transaction->getOrderStatuses();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->loadView('extension/postfinancecheckout/transaction', $data));
	}

	protected function getRequiredPermission(){
		return 'extension/postfinancecheckout/transaction';
	}

	private function getFilters(){
		$filters = array(
			'id' => null,
			'order_id' => null,
			'transaction_id' => null,
			'space_id' => null,
			'space_view_id' => null,
			'state' => null,
			'payment_method_id' => null,
			'authorization_amount' => null,
			'created_at' => null,
			'updated_at' => null,
			'sort' => $this->getSort(),
			'page' => $this->getPage(),
			'order' => $this->getOrder() 
		);
		foreach ($filters as $filterName => $filter) {
			if (isset($this->request->post['filters'][$filterName])) {
				$filters[$filterName] = $this->request->post['filters'][$filterName];
			}
			elseif (isset($this->request->get['filters'][$filterName])) {
				$filters[$filterName] = $this->request->get['filters'][$filterName];
			}
		}
		return $filters;
	}

	private function getQueryString(array $filters){
		$url = '';
		foreach ($filters as $name => $value) {
			if ($value) {
				$url .= '&filters[' . $name . ']=' . $value;
			}
		}
		$url .= '&user_token=' . $this->session->data['user_token'];
		return $url;
	}

	private function getSortLinks($filters){
		$sortable = array(
			'id',
			'order_id',
			'transaction_id',
			'space_id',
			'space_view_id',
			'state',
			'payment_method_id',
			'authorization_amount',
			'created_at',
			'updated_at' 
		);
		$filters['sort'] = null;
		$filters['order'] = $this->getNewOrder($filters);
		$query = $this->getQueryString($filters);
		$sortUrl = PostFinanceCheckoutVersionHelper::createUrl($this->url, 'extension/postfinancecheckout/transaction', $query, true);
		$links = array();
		foreach ($sortable as $key) {
			$links['sort_' . $key] = $sortUrl . '&filters[sort]=' . $key;
		}
		return $links;
	}

	private function getNewOrder(array $filters){
		if (isset($filters['order']) && $filters['order'] == 'DESC') {
			return 'ASC';
		}
		return 'DESC';
	}

	private function getPagination(array $filters, $transactionCount){
		$pagination = new Pagination();
		$pagination->total = $transactionCount;
		$pagination->page = $filters['page'];
		$pagination->limit = $this->config->get('config_limit_admin');
		$filters['page'] = '{page}';
		$pagination->url = PostFinanceCheckoutVersionHelper::createUrl($this->url, 'extension/postfinancecheckout/transaction',
				$this->getQueryString($filters), true);
		return $pagination;
	}

	private function getResultsText($transactionCount, $page){
		$limit = $this->config->get('config_limit_admin');
		return sprintf($this->language->get('text_pagination'), ($transactionCount) ? (($page - 1) * $limit) + 1 : 0,
				((($page - 1) * $limit) > ($transactionCount - $limit)) ? $transactionCount : ((($page - 1) * $limit) + $limit), $transactionCount,
				ceil($transactionCount / $limit));
	}

	private function getOrder(){
		if (isset($this->request->get['filters']['order'])) {
			return $this->request->get['filters']['order'];
		}
		return 'DESC';
	}

	private function getPage(){
		if (isset($this->request->get['filters']['page'])) {
			return $this->request->get['filters']['page'];
		}
		return 1;
	}

	private function getSort(){
		if (isset($this->request->get['filters']['sort'])) {
			return $this->request->get['filters']['sort'];
		}
		return 'id';
	}

	private function getBreadcrumbs(){
		return array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => PostFinanceCheckoutVersionHelper::createUrl($this->url, 'sale/order',
						array(
							'user_token' => $this->session->data['user_token'] 
						), true) 
			),
			array(
				'text' => $this->language->get('heading_transaction_list'),
				'href' => PostFinanceCheckoutVersionHelper::createUrl($this->url, 'extension/postfinancecheckout/transaction',
						array(
							'user_token' => $this->session->data['user_token'] 
						), true) 
			) 
		);
	}

	private function loadLanguageVariables(){
		//TODO  * NOTE: Filter works by id, e.g. searching by 'credit card' doesn't work.
		$required = array(
			'heading_transaction_list',
			'text_transaction_list',
			'text_no_results',
			'column_id',
			'column_order_id',
			'column_transaction_id',
			'column_space_view_id',
			'column_space_id',
			'column_state',
			'column_payment_method',
			'column_authorization_amount',
			'column_created',
			'column_updated',
			'column_actions',
			'description_payment_method',
			'button_view',
			'button_filter' 
		);
		$translations = array();
		foreach ($required as $key) {
			$translations[$key] = $this->language->get($key);
		}
		return $translations;
	}
}