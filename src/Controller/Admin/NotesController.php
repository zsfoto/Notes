<?php
// Baked at 2023.09.06. 14:37:02
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

/**
 * Notes Controller
 *
 * @property \App\Model\Table\NotesTable $Notes
 * @method \App\Model\Entity\Note[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class NotesController extends AppController
{

    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
		$this->set('title', __('Notes'));
		
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
		$notes = null;
		
		$this->set('title', __('Notes'));

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
            'contain' => ['Categories'],
			'conditions' => [
				//'Notes.name' 		=> 1,
				//'Notes.visible' 		=> 1,
				//'Notes.created >= ' 	=> new \DateTime('-10 days'),
				//'Notes.modified >= '	=> new \DateTime('-10 days'),
			],
			/*
			// Nem tanácsos az order-t itt használni, mert pl az edit után az utolsó  ordert ugyan beálíltja, de
			// kiegészíti ezzel s így az utoljára mentett rekord nem lesz megtalálható az X-edik oldalon, mert az az elsőre kerül.
			// A felhasználó állítson be rendezettséget magának! Kivételes esetek persze lehetnek!
			*/
			'order' => [
				//'Notes.id' 			=> 'desc',
				//'Notes.name' 		=> 'asc',
				//'Notes.visible' 		=> 'desc',
				//'Notes.pos' 			=> 'desc',
				//'Notes.rank' 		=> 'asc',
				//'Notes.created' 		=> 'desc',
				//'Notes.modified' 	=> 'desc',
			],
			'limit' => $this->config['index_number_of_rows'],
			'maxLimit' => $this->config['index_number_of_rows'],
			//'sortableFields' => ['id', 'name', 'created', '...'],
			//'paramType' => 'querystring',
			//'fields' => ['Notes.id', 'Notes.name', ...],
			//'finder' => 'published',
        ];

		//$this->paging = $this->session->read('Layout.' . $this->controller . '.Paging');

		if( $this->paging === null){
			$this->paginate['order'] = [
				//'Notes.id' 			=> 'desc',
				//'Notes.name' 		=> 'asc',
				//'Notes.visible' 		=> 'desc',
				//'Notes.pos' 			=> 'desc',
				//'Notes.rank' 		=> 'asc',
				//'Notes.created' 		=> 'desc',
				//'Notes.modified' 	=> 'desc',
			];
		}else{
			if($this->request->getQuery('sort') === null && $this->request->getQuery('direction') === null){
				$this->paginate['order'] = [
					// If not in URL-ben, then come from sessinon...
					$this->paging['Notes']['sort'] => $this->paging['Notes']['direction']	
				];
			}
		}

		if($this->request->getQuery('page') === null && !isset($this->paging['Notes']['page']) ){
			$this->paginate['page'] = 1;
		}else{
			$this->paginate['page'] = (isset($this->paging['Notes']['page'])) ? $this->paging['Notes']['page'] : 1;
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
						//	'page'		=> $this->paging['Notes']['page'], 	// Vagy 1
						//	'sort'		=> $this->paging['Notes']['sort'], 
						//	'direction'	=> $this->paging['Notes']['direction'],
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
			//$this->paginate['conditions'] = ['Notes.name LIKE' => $q ];
			$this->paginate['conditions'][] = [
				'OR' => [
					['Notes.name LIKE' => $search['s'] ],
					//['Notes.title LIKE' => $search['s'] ], // ... just add more fields
				]
			];
			
		}
		// -- /.Filter --
		
		try {
			$notes = $this->paginate($this->Notes);
		} catch (NotFoundException $e) {
			$paging = $this->request->getAttribute('paging');
			if($paging['Notes']['prevPage'] !== null && $paging['Notes']['prevPage']){
				if($paging['Notes']['page'] !== null && $paging['Notes']['page'] > 0){
					return $this->redirect([
						'controller' => $this->controller, 
						'action' => 'index', 
						'?' => [
							'page'		=> 1,	//$this->paging['Notes']['page'],
							'sort'		=> $this->paging['Notes']['sort'],
							'direction'	=> $this->paging['Notes']['direction'],
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
		$this->set(compact('notes'));
		
	}


    /**
     * View method
     *
     * @param string|null $id Note id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
		$this->set('title', __('Note'));
		
        $note = $this->Notes->get($id, [
            'contain' => ['Categories'],
        ]);

		$this->session->write('Layout.' . $this->controller . '.LastId', $id);

		$name = $note->name;

        $this->set(compact('note', 'id', 'name'));
    }


    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
		$this->set('title', __('Note'));
        $note = $this->Notes->newEmptyEntity();
        if ($this->request->is('post')) {
            $note = $this->Notes->patchEntity($note, $this->request->getData());
            if ($this->Notes->save($note)) {
                //$this->Flash->success(__('The note has been saved.'));
                $this->Flash->success(__('Has been saved.'));

				$this->session->write('Layout.' . $this->controller . '.LastId', $note->id);
	
                //return $this->redirect(['action' => 'index']);
                return $this->redirect([
					'controller' => $this->controller, 
					'action' => 'index', 
					'?' => [
						'page'		=> 1,
						'sort'		=> 'id',
						'direction'	=> 'desc',
					],
					'#' => $note->id	// Az állandó header miatt takarásban van még. Majd...
				]);

                return $this->redirect(['action' => 'index']);
            }
            //$this->Flash->error(__('The note could not be saved. Please, try again.'));
			$this->Flash->error(__('Could not be saved. Please, try again.'));
        }
        //$categories = $this->Notes->Categories->find('list', ['limit' => 200]);	// Original
		//$categories = $this->Notes->Categories->find('list', ['limit' => 200, 'conditions'=>['Categories.visible' => 1], 'order'=>['Categories.pos' => 'asc', 'Categories.name' => 'asc']]);
		$categories = $this->Notes->Categories->find('list', ['limit' => 200, 'order'=>['Categories.pos' => 'asc', 'Categories.name' => 'asc']]);
        $this->set(compact('note', 'categories'));
    }


    /**
     * Edit method
     *
     * @param string|null $id Note id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
		$this->set('title', __('Note'));
        $note = $this->Notes->get($id, [
            'contain' => [],
        ]);

		$this->session->write('Layout.' . $this->controller . '.LastId', $id);

        if ($this->request->is(['patch', 'post', 'put'])) {
			//debug($this->request->getData()); //die();
            $note = $this->Notes->patchEntity($note, $this->request->getData());
            //debug($note); //die();
			if ($this->Notes->save($note)) {
                //$this->Flash->success(__('The note has been saved.'));
                $this->Flash->success(__('Has been saved.'));
				
				//return $this->redirect(['action' => 'index']);
                return $this->redirect([
					'controller' => $this->controller, 
					'action' => 'index', 
					'?' => [
						'page'		=> (isset($this->paging['Notes']['page'])) ? $this->paging['Notes']['page'] : 1, 		// or 1
						'sort'		=> (isset($this->paging['Notes']['sort'])) ? $this->paging['Notes']['sort'] : 'created', 
						'direction'	=> (isset($this->paging['Notes']['direction'])) ? $this->paging['Notes']['direction'] : 'desc',
					],
					'#' => $id
				]);
				
            }
            //$this->Flash->error(__('The note could not be saved. Please, try again.'));
            $this->Flash->error(__('Could not be saved. Please, try again.'));
        }
        //$categories = $this->Notes->Categories->find('list', ['limit' => 200]);
		//$categories = $this->Notes->Categories->find('list', ['limit' => 200, 'conditions'=>['Categories.visible' => 1], 'order'=>['Categories.pos' => 'asc', 'Categories.name' => 'asc']]);
		$categories = $this->Notes->Categories->find('list', ['limit' => 200, 'order'=>['Categories.pos' => 'asc', 'Categories.name' => 'asc']]);

		$name = $note->name;

        $this->set(compact('note', 'categories', 'id', 'name'));
    }


    /**
     * Delete method
     *
     * @param string|null $id Note id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $note = $this->Notes->get($id);
        if ($this->Notes->delete($note)) {
            //$this->Flash->success(__('The note has been deleted.'));
            $this->Flash->success(__('Has been deleted.'));
        } else {
            //$this->Flash->error(__('The note could not be deleted. Please, try again.'));
            $this->Flash->error(__('Could not be deleted. Please, try again.'));
        }

        //return $this->redirect(['action' => 'index']);
		return $this->redirect([
			'controller' => $this->controller, 
			'action' => 'index', 
			'?' => [
				'page'		=> $this->paging['Notes']['page'], 
				'sort'		=> $this->paging['Notes']['sort'], 
				'direction'	=> $this->paging['Notes']['direction'],
			]
		]);
		
    }

}

