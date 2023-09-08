<?php
// Baked at 2023.09.06. 15:23:46
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

/**
 * Categories Controller
 *
 * @property \App\Model\Table\CategoriesTable $Categories
 * @method \App\Model\Entity\Category[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CategoriesController extends AppController
{

    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
		$this->set('title', __('Categories'));
		
	}
	
    /**
     * Index method
     *
	 * @param string|null $param: if($param !== null && $param == 'clear-filter')...
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index($param = null)
    {
		$search = null;
		$categories = null;
		
		$this->set('title', __('Categories'));

		//$this->config['index_number_of_rows'] = 10;
		if($this->config['index_number_of_rows'] === null){
			$this->config['index_number_of_rows'] = 20;
		}
		
		// Clear filter from session
		if($param !== null && $param == 'clear-filter'){
			$this->session->delete('Layout.' . $this->controller . '.Search');
			$this->redirect( $this->request->referer() );
		}		
		
        $this->paginate = [
			'conditions' => [
				//'Categories.name' 		=> 1,
				//'Categories.visible' 		=> 1,
				//'Categories.created >= ' 	=> new \DateTime('-10 days'),
				//'Categories.modified >= '	=> new \DateTime('-10 days'),
			],
			/*
			// Nem tanácsos az order-t itt használni, mert pl az edit után az utolsó  ordert ugyan beálíltja, de
			// kiegészíti ezzel s így az utoljára mentett rekord nem lesz megtalálható az X-edik oldalon, mert az az elsőre kerül.
			// A felhasználó állítson be rendezettséget magának! Kivételes esetek persze lehetnek!
			*/
			'order' => [
				//'Categories.id' 			=> 'desc',
				//'Categories.name' 		=> 'asc',
				//'Categories.visible' 		=> 'desc',
				//'Categories.pos' 			=> 'desc',
				//'Categories.rank' 		=> 'asc',
				//'Categories.created' 		=> 'desc',
				//'Categories.modified' 	=> 'desc',
			],
			'limit' => $this->config['index_number_of_rows'],
			'maxLimit' => $this->config['index_number_of_rows'],
			//'sortableFields' => ['id', 'name', 'created', '...'],
			//'paramType' => 'querystring',
			//'fields' => ['Categories.id', 'Categories.name', ...],
			//'finder' => 'published',
        ];

		//$this->paging = $this->session->read('Layout.' . $this->controller . '.Paging');

		if( $this->paging === null){
			$this->paginate['order'] = [
				//'Categories.id' 			=> 'desc',
				//'Categories.name' 		=> 'asc',
				//'Categories.visible' 		=> 'desc',
				//'Categories.pos' 			=> 'desc',
				//'Categories.rank' 		=> 'asc',
				//'Categories.created' 		=> 'desc',
				//'Categories.modified' 	=> 'desc',
			];
		}else{
			if($this->request->getQuery('sort') === null && $this->request->getQuery('direction') === null){
				$this->paginate['order'] = [
					// If not in URL-ben, then come from sessinon...
					$this->paging['Categories']['sort'] => $this->paging['Categories']['direction']	
				];
			}
		}

		if($this->request->getQuery('page') === null && !isset($this->paging['Categories']['page']) ){
			$this->paginate['page'] = 1;
		}else{
			$this->paginate['page'] = (isset($this->paging['Categories']['page'])) ? $this->paging['Categories']['page'] : 1;
		}
		
		// -- Filter --
		if ($this->request->is('post') || $this->session->read('Layout.' . $this->controller . '.Search') !== null && $this->session->read('Layout.' . $this->controller . '.Search') !== []) {
				
			if( $this->request->is('post') ){
				$search = $this->request->getData();
				$this->session->write('Layout.' . $this->controller . '.Search', $search);
				if($search !== null && $search['s'] !== null && $search['s'] == ''){
					$this->session->delete('Layout.' . $this->controller . '.Search');
					return $this->redirect([
						'controller' => $this->controller, 
						'action' => 'index', 
						//'?' => [			// Not tested!!!
						//	'page'		=> $this->paging['Categories']['page'], 	// Vagy 1
						//	'sort'		=> $this->paging['Categories']['sort'], 
						//	'direction'	=> $this->paging['Categories']['direction'],
						//]
					]);
				}
			}else{
				if($this->session->check('Layout.' . $this->controller . '.Search')){
					$search = $this->session->read('Layout.' . $this->controller . '.Search');
				}
			}

			$this->set('search', $search['s']);
			
			$search['s'] = '%'.str_replace(' ', '%', $search['s']).'%';
			//$this->paginate['conditions'] = ['Categories.name LIKE' => $q ];
			$this->paginate['conditions'][] = [
				'OR' => [
					['Categories.name LIKE' => $search['s'] ],
					//['Categories.title LIKE' => $search['s'] ], // ... just add more fields
				]
			];
			
		}
		// -- /.Filter --
		
		try {
			$categories = $this->paginate($this->Categories);
		} catch (NotFoundException $e) {
			$paging = $this->request->getAttribute('paging');
			if($paging['Categories']['prevPage'] !== null && $paging['Categories']['prevPage']){
				if($paging['Categories']['page'] !== null && $paging['Categories']['page'] > 0){
					return $this->redirect([
						'controller' => $this->controller, 
						'action' => 'index', 
						'?' => [
							'page'		=> 1,	//$this->paging['Categories']['page'],
							'sort'		=> $this->paging['Categories']['sort'],
							'direction'	=> $this->paging['Categories']['direction'],
						],
					]);			
				}
			}
			
		}

		$paging = $this->request->getAttribute('paging');

		if($this->paging !== $paging){
			$this->paging = $paging;
			$this->session->write('Layout.' . $this->controller . '.Paging', $paging);
		}

		$this->set('paging', $this->paging);
		$this->set('layout' . $this->controller . 'LastId', $this->session->read('Layout.' . $this->controller . '.LastId'));
		$this->set(compact('categories'));
		
	}


    /**
     * View method
     *
     * @param string|null $id Category id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
		$this->set('title', __('Category'));
		
        $category = $this->Categories->get($id, [
            'contain' => ['Notes'],
        ]);

		$this->session->write('Layout.' . $this->controller . '.LastId', $id);

		$name = $category->name;

        $this->set(compact('category', 'id', 'name'));
    }


    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
		$this->set('title', __('Category'));
        $category = $this->Categories->newEmptyEntity();
        if ($this->request->is('post')) {
            $category = $this->Categories->patchEntity($category, $this->request->getData());
            if ($this->Categories->save($category)) {
                //$this->Flash->success(__('The category has been saved.'));
                $this->Flash->success(__('Has been saved.'));

				$this->session->write('Layout.' . $this->controller . '.LastId', $category->id);
	
                //return $this->redirect(['action' => 'index']);
                return $this->redirect([
					'controller' => $this->controller, 
					'action' => 'index', 
					'?' => [
						'page'		=> 1,
						'sort'		=> 'id',
						'direction'	=> 'desc',
					],
					'#' => $category->id	// Az állandó header miatt takarásban van még. Majd...
				]);

                return $this->redirect(['action' => 'index']);
            }
            //$this->Flash->error(__('The category could not be saved. Please, try again.'));
			$this->Flash->error(__('Could not be saved. Please, try again.'));
        }
        $this->set(compact('category'));
    }


    /**
     * Edit method
     *
     * @param string|null $id Category id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
		$this->set('title', __('Category'));
        $category = $this->Categories->get($id, [
            'contain' => [],
        ]);

		$this->session->write('Layout.' . $this->controller . '.LastId', $id);

        if ($this->request->is(['patch', 'post', 'put'])) {
			//debug($this->request->getData()); //die();
            $category = $this->Categories->patchEntity($category, $this->request->getData());
            //debug($category); //die();
			if ($this->Categories->save($category)) {
                //$this->Flash->success(__('The category has been saved.'));
                $this->Flash->success(__('Has been saved.'));
				
				//return $this->redirect(['action' => 'index']);
                return $this->redirect([
					'controller' => $this->controller, 
					'action' => 'index', 
					'?' => [
						'page'		=> (isset($this->paging['Categories']['page'])) ? $this->paging['Categories']['page'] : 1, 		// or 1
						'sort'		=> (isset($this->paging['Categories']['sort'])) ? $this->paging['Categories']['sort'] : 'created', 
						'direction'	=> (isset($this->paging['Categories']['direction'])) ? $this->paging['Categories']['direction'] : 'desc',
					],
					'#' => $id
				]);
				
            }
            //$this->Flash->error(__('The category could not be saved. Please, try again.'));
            $this->Flash->error(__('Could not be saved. Please, try again.'));
        }

		$name = $category->name;

        $this->set(compact('category', 'id', 'name'));
    }


    /**
     * Delete method
     *
     * @param string|null $id Category id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $category = $this->Categories->get($id);
        if ($this->Categories->delete($category)) {
            //$this->Flash->success(__('The category has been deleted.'));
            $this->Flash->success(__('Has been deleted.'));
        } else {
            //$this->Flash->error(__('The category could not be deleted. Please, try again.'));
            $this->Flash->error(__('Could not be deleted. Please, try again.'));
        }

        //return $this->redirect(['action' => 'index']);
		return $this->redirect([
			'controller' => $this->controller, 
			'action' => 'index', 
			'?' => [
				'page'		=> $this->paging['Categories']['page'], 
				'sort'		=> $this->paging['Categories']['sort'], 
				'direction'	=> $this->paging['Categories']['direction'],
			]
		]);
		
    }

}

