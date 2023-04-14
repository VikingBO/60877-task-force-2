<?php

namespace app\controllers;

use app\models\AddReply;
use app\models\AddTask;
use app\models\AddTaskReply;
use app\models\Category;
use app\models\City;
use app\models\Files;
use app\models\FinishReply;
use app\models\Replies;
use app\models\SearchTasks;
use app\models\Task;
use app\models\TasksReply;
use app\models\TaskStatuses;
use app\models\UserReply;
use yii\base\BaseObject;
use yii\data\Pagination;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use Yii;
use app\models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


class TasksController extends SecuredController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ],
                    [
                        'allow' => false,
                        'actions' => ['add'],
                        'matchCallback' => function ($rule, $action) {
                            return \Yii::$app->user->identity->user_status === User::EXECUTOR;
                        }
                    ]
                ]
            ]
        ];
    }

    public function actionIndex()
    {

        $categories = Category::find()->all();

        $modelSearch = new SearchTasks();

        $dataProvider = $modelSearch->search($this->request->post());


        return $this->render('index', [
            'modelSearch' => $modelSearch,
            'dataProvider' => $dataProvider,
            'categories' => $categories,
        ]);

    }

    public function actionMyTasks()
    {
        $my_tasks = Task::find()->where(['user_id' => \Yii::$app->user->identity->id])->all();

        $request = Yii::$app->request;

        $status = $request->get('status');


        $my_tasks_new = Task::find()->where(['user_id' => \Yii::$app->user->identity->id])->andWhere(['status' => 'new'])->all();
        $my_tasks_in_process = Task::find()->where(['user_id' => \Yii::$app->user->identity->id])->andWhere(['status' => 'in_progress'])->all();
        $my_tasks_done = Task::find()->where(['user_id' => \Yii::$app->user->identity->id])->andWhere(['status' => 'done'])->all();
        return $this->render('my-tasks', ['my_tasks' => $my_tasks, 'my_tasks_new' => $my_tasks_new, 'my_tasks_in_process' => $my_tasks_in_process, 'my_tasks_done' => $my_tasks_done, 'status' => $status]);

    }

    public function actionView(int $id)
    {
        $task = Task::find()->where(['id' => $id])->one();

        if ($task === null) {
            throw new NotFoundHttpException("Нету такого таска!");
        }

        $userModel = User::find()->where(['id' => $task->user_id])->one();
        $taskOwnerStatus = $userModel->user_status;

        $taskReply = TasksReply::find()->where([
            'user_id'=>Yii::$app->user->identity->id,
            'task_id' => $id
        ])->one();

        /* $reply_status = $reply_stat->status;

         if ($reply_status === 'null') {
             $reply_status = 'new';
         }*/

        $files = Files::find()->where(['tasks_id' => Yii::$app->request->get('id')])->all();


        return $this->render(
            'single-task',
            [
                'task' => $task,
                'userModel' => $userModel,
                'taskReply'=>$taskReply,
                /*'reply_status' => $reply_status,*/
                'taskOwnerStatus' => $taskOwnerStatus,
                'files' => $files,
                'id' => $id
            ]);
    }

    public function actionAdd()
    {
        $categories = Category::find()->all();
        $model = new AddTask();
        $formData = \Yii::$app->request->post();

        if ($model->load($formData) && $model->validate()) {
            try {
                $task = $model->createNewTask();
            } catch (RequestException $e) {
                throw new Exception('Can`t add new task');
            }

            \Yii::$app->response->redirect(['/tasks/view/', 'id' => $task->id]);
        }

        return $this->render('add', ['model' => $model, 'categories' => $categories]);
    }


    public function actionAddReply($id)
    {
        $taskModel = new AddReply(); // модель формы

        $formData = \Yii::$app->request->post();
        /** @var Task $task */
        $task = Task::find()->where(['id' => $id])->one();
        $user = \Yii::$app->user->identity;

        $formData["AddReply"]['taskStatus'] = $task->status;
        $formData["AddReply"]['userStatus'] = $user->user_status;
        if ($taskModel->load($formData) && $taskModel->validate() /*&& $userStatus === 'executor' && $taskStatus === 'new'*/) {

            $newReply = new TasksReply();
            $newReply->dt_add = date("Y-m-d H:i:s");
            $newReply->description = $taskModel->your_comment;
            $newReply->task_id = (int)$id;
            $newReply->price = $taskModel->price;
            $newReply->user_id = (int)$user->id;
            if ($newReply->save()) {
                return $this->redirect('/tasks/view/' . $id);
            } else {
                var_dump($newReply->getErrors());
                var_dump("Тут ошибка!");
                return $this->redirect('/tasks/view/' . $id);
            }

        }

    }

    public function actionFinishTask($id)
    {
        $taskModel = new FinishReply();
        $formData = \Yii::$app->request->post();
        $user = \Yii::$app->user->identity;
        $task = Task::find()->where(['id' => $id])->one();
        if ($taskModel->load($formData) && $taskModel->validate()) {
            $newUserReply = new UserReply();

            $newUserReply->create_at = date("Y-m-d H:i:s");
            $newUserReply->rate = $taskModel->finish_task_rate;
            $newUserReply->description = $taskModel->your_comment_finish_task;
            $newUserReply->task_id = (int)$id;
            $newUserReply->executor_id = $task->executor_id;
            $newUserReply->user_id = (int)$user->id;
            /** @var Task $task */
            $task->status = Task::STATUS_DONE;
            $task->save();
            if ($newUserReply->save()) {
                return $this->redirect('/tasks/view/' . $id);
            } else {
                var_dump($newUserReply->getErrors());
            }
        }
    }


    public function actionRejectedTask($id)
    {
        $rejectedTask = Task::find()->where(['id' => $id])->one();
        if ($rejectedTask->status === 'in_progress' && $rejectedTask->user_id = \Yii::$app->user->identity->id) {
            $rejectedTask->status = Task::STATUS_FAILED;
            $rejectedTask->save();
            return $this->redirect('/tasks/view/' . $id);
        } else {
            throw new \Exception('Не смогли отменить задание');
        }

    }

//действия с кнопками на отзывах
    public function actionAcceptTaskReply()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $user_id = $request->get('user_id');
        $changeTaskStatus = Task::find()->where(['id' => $id])->one();
        $changeTaskStatus->executor_id = $user_id;
        $changeTaskStatus->status = TasksReply::STATUS_INPROGRESS;
        $changeTaskStatus->save();
        return $this->redirect('/tasks/view/' . $id);
    }

    public function actionRejectTaskReply()
    {
        $request = Yii::$app->request;
        $task_id = $request->get('task_id');
        $user_id = $request->get('user_id');
        $reply_status = TasksReply::STATUS_REJECTED;
        $changeReplyStatus = TasksReply::find()->where(['task_id' => $task_id])->one();

        $changeReplyStatus->status = $reply_status;

        if ($changeReplyStatus->save()) {

            return $this->redirect('/tasks/view/' . $task_id);
        } else {
            throw new \Exception('Не смогли отказать юзеру!');
        }

    }


}


