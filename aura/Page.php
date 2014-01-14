<?php
namespace Hari\Sample\Web\Post;

use Hari\Framework\Web\PageController;

class Page extends PageController
{
    public function actionList()
    {
        $page = $this->getContext()->getQuery('page', 1);
        $post = $this->getFactory()->newInstance('hari.sample.model.post');
        $posts = $post->findAll($page);
        $this->data->posts = $posts;
        // if you have multiple alyouts for different formats
        $this->layout = [
            '.html' => 'default',
            '.json' => '',
            '.xml' => ''
        ];
        
        // if you have multiple views for different formats
        $this->view = [
            '.html' => 'list',
            '.json' => function () use ($posts) {
                return 'Hello';
                // return json_encode($posts, JSON_PRETTY_PRINT);
            },
            '.xml' => 'list.xml'
        ];            
    }

    public function actionAdd()
    {
        $segment = $this->session_manager->newSegment('User');
        if (! isset($segment->user)) {
            // We want to redirect to user_login page.
            $this->getResponse()->setRedirect($this->router->generate('user_login'));
        }        
        $this->view = 'add';
        $this->layout = 'default';
        $form = $this->getFactory()->newInstance('hari.sample.postform');
        if ($this->getContext()->isPost()) {
            $data = $this->getContext()->getPost();
            // validate csrf token
            $form->fill($data);
            if ($form->filter()) {
                $post = new Post();
                $post->setTitle($data['title']);
                $post->setBody($data['body']);
                $post->setSlug($data['slug']);
                $id = $segment->user->getId();
                $post->setUserId($id);                
                $tags = explode(',', $data['tags']);
                foreach ($tags as $tag) {
                    $tagobj = TagQuery::create()->findOneByName($tag);
                    if (! $tagobj) {
                        $tagobj = new Tag();
                        $tagobj->setName($tag);
                        $tagobj->save();
                    }
                    $post->addTag($tagobj);
                }
                $post->save();
                $this->getResponse()->setRedirect($this->router->generate('post_list'));
            }
        }
        $this->data->title = 'Add post';
        $this->data->form = $form;
        $this->data->form_action = $this->router->generate('post_add');
    }

    public function actionEdit()
    {
        $this->view = 'add';
        $this->layout = 'default';
        $id = $this->params['id'];
        $post = PostQuery::create()->findOneById($id);
        if (! $post) {
            $this->getResponse()->setRedirect($this->router->generate('post_list'));
        }
        $form = $this->getFactory()->newInstance('hari.sample.postform');
        if ($this->getContext()->isPost()) {
            $data = $this->getContext()->getPost();
            // validate csrf token
            $form->fill($data);
            if ($form->filter()) {
                $post->setTitle($data['title']);
                $post->setBody($data['body']);
                $post->setSlug($data['slug']);
                $tags = new \Propel\Runtime\Collection\ObjectCollection();                                
                foreach (explode(',', $data['tags']) as $tag) {
                    $tags->append(TagQuery::create()->filterByName($tag)->findOneOrCreate());
                }
                $post->setTags($tags);
                if ($post->save()) {
                    $this->getResponse()->setRedirect($this->router->generate('post_list'));
                }
            }
        } else {
            $data = $post->toArray(PostTableMap::TYPE_FIELDNAME);
            $tags = $post->getTags();
            $tagname = array();
            foreach ($tags as $tag) {
                $tagname[] = $tag->getName();
            }
            $data['tags'] = implode(',', $tagname);
            $form->fill($data);
        }
        $this->data->title = 'Edit post'; 
        $this->data->form = $form;
        $this->data->form_action = $this->router->generate('post_edit', array('id', $id));
    }

    public function actionView()
    {
        $id = $this->params['id'];
        $post_model = $this->getFactory()->newInstance('hari.sample.model.post');
        $post = $post_model->findByIDWithUser($id);
        if (! $post) {
            // $this->getResponse()->setRedirect($this->router->generate('post_list'));
        } 
        $form = $this->getFactory()->newInstance('post.comment');
        $comment = $this->getFactory()->newInstance('hari.sample.model.comment');
        if ($this->getContext()->isPost()) {
            $data = $this->getContext()->getPost();
            $form->fill($data);
            if ($form->filter()) {                
                $comment->body = $data['body'];
                $comment->name = $data['name'];
                $comment->email = $data['email'];
                $comment->post_id = $id;
                if ($comment->save()) {
                    $this->getResponse()->setRedirect(
                        $this->router->generate('post_view', ['id' => $id])
                    );
                }
            }
        }
        $comments = $comment->findAll($id);
        $this->data->form = $form;
        $this->data->post = $post;
        $this->data->comments = $comments;
        $this->layout = 'default';
        $this->view = 'view';
    }

    public function actionByTag()
    {
        $tagname = $this->params['tag'];
        $tag = TagQuery::create()->findOneBySlug($tagname);
        if ($tag) {
            $page = $this->getContext()->getQuery('page', 1);
            $posts = PostQuery::create()
                        ->filterByTag($tag)
                        ->orderByCreatedAt('DESC')
                        ->limit(10)
                        ->find();
        } else {
        }        
        $this->data->posts = $posts;
        $this->layout = 'default';
        $this->view = 'list';
    }
}
