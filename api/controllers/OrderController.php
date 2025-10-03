<?php

namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\VerbFilter;
use common\models\Order;
use common\models\OutboxMessage;
use common\behaviors\ApiProtectionBehavior;

/**
 * Order Controller
 */
class OrderController extends ActiveController
{
    public $modelClass = 'common\models\Order';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
            ],
        ];

        $behaviors['apiProtection'] = [
            'class' => ApiProtectionBehavior::class,
        ];

        return $behaviors;
    }

    /**
     * Create order action
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Order();
        $model->load(Yii::$app->request->post(), '');

        // Generate order number
        $model->order_number = 'ORD-' . strtoupper(uniqid());
        $model->status = Order::STATUS_PENDING;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$model->save()) {
                $transaction->rollBack();
                Yii::$app->response->statusCode = 422;
                return [
                    'success' => false,
                    'errors' => $model->errors,
                ];
            }

            // Create outbox message for event
            $outboxMessage = new OutboxMessage();
            $outboxMessage->aggregate_id = (string)$model->id;
            $outboxMessage->aggregate_type = 'Order';
            $outboxMessage->event_type = 'OrderCreated';
            $outboxMessage->setPayloadArray([
                'order_id' => $model->id,
                'order_number' => $model->order_number,
                'user_id' => $model->user_id,
                'total_amount' => $model->total_amount,
                'items' => $model->getItemsArray(),
            ]);
            $outboxMessage->status = OutboxMessage::STATUS_PENDING;

            if (!$outboxMessage->save()) {
                $transaction->rollBack();
                Yii::$app->response->statusCode = 500;
                return [
                    'success' => false,
                    'message' => 'Failed to create outbox message',
                ];
            }

            $transaction->commit();

            // Record metrics
            if (Yii::$app->has('metricsCollector')) {
                Yii::$app->metricsCollector->increment('order_created');
            }

            return [
                'success' => true,
                'data' => $model,
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Order creation failed: " . $e->getMessage(), __METHOD__);

            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Internal server error',
            ];
        }
    }

    /**
     * List orders action
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = Order::find();

        // Add filters
        if ($userId = Yii::$app->request->get('user_id')) {
            $query->andWhere(['user_id' => $userId]);
        }

        if ($status = Yii::$app->request->get('status')) {
            $query->andWhere(['status' => $status]);
        }

        // Pagination
        $page = max(1, (int)Yii::$app->request->get('page', 1));
        $perPage = min(100, (int)Yii::$app->request->get('per_page', 20));

        $orders = $query
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->all();

        $total = $query->count();

        return [
            'success' => true,
            'data' => $orders,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
            ],
        ];
    }

    /**
     * View order action
     */
    public function actionView($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Order::findOne($id);

        if (!$model) {
            Yii::$app->response->statusCode = 404;
            return [
                'success' => false,
                'message' => 'Order not found',
            ];
        }

        return [
            'success' => true,
            'data' => $model,
        ];
    }
}
