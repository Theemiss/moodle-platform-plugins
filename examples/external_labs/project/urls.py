from django.urls import path, include
from rest_framework.routers import DefaultRouter
from .views import (
    ProjectListView, ProjectDetailView,
    TaskListView, TaskDetailView,
    CheckerListView, CheckerDetailView,
    ResourceListView, ResourceDetailView,
    QuizListView, QuizDetailView,
    TaskGradeCreateUpdateView, ProjectGradeView,MoodleAPIConfigViewSet, AWSAccountViewSet, AwsMoodleUserViewSet,MoodleCourceProjectViewSet

)
router = DefaultRouter()
router.register(r'moodle-configs', MoodleAPIConfigViewSet, basename='moodle-config')
router.register(r'aws-accounts', AWSAccountViewSet, basename='aws-account')
router.register(r'aws-moodle-users', AwsMoodleUserViewSet, basename='aws-moodle-user')
router.register(r'moodle-course-projects', MoodleCourceProjectViewSet, basename='moodle-course-project')
urlpatterns = [
    # Projects
    path('projects/', ProjectListView.as_view(), name='project-list'),
    path('projects/<int:pk>/', ProjectDetailView.as_view(), name='project-detail'),

    # Tasks
    path('projects/<int:project_id>/tasks/', TaskListView.as_view(), name='task-list'),
    path('tasks/<int:pk>/', TaskDetailView.as_view(), name='task-detail'),

    # Checkers
    path('tasks/<int:task_id>/checkers/', CheckerListView.as_view(), name='checker-list'),
    path('checkers/<int:pk>/', CheckerDetailView.as_view(), name='checker-detail'),

    # Resources
    path('projects/<int:project_id>/resources/', ResourceListView.as_view(), name='resource-list'),
    path('resources/<int:pk>/', ResourceDetailView.as_view(), name='resource-detail'),

    # Quizzes
    path('projects/<int:project_id>/quizzes/', QuizListView.as_view(), name='quiz-list'),
    path('quizzes/<int:pk>/', QuizDetailView.as_view(), name='quiz-detail'),

    # Grades
    path('projects/<int:project_id>/tasks/<int:task_id>/grade/', TaskGradeCreateUpdateView.as_view(), name='task-grade'),
    path('projects/<int:project_id>/grade/', ProjectGradeView.as_view(), name='project-grade'),
    path('', include(router.urls)),
]
