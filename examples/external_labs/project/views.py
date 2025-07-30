from rest_framework import generics, permissions, status
from rest_framework.views import APIView
from rest_framework.response import Response
from django.shortcuts import get_object_or_404
from django.contrib.auth import get_user_model
from rest_framework import viewsets
from .models import Project, Task, Checker, Resource, Quiz, TaskGrade, MoodleAPIConfig, AWSAccount, AwsMoodleUser, MoodleCourceProject
from .serializers import (
    ProjectSerializer, TaskSerializer, CheckerSerializer, ResourceSerializer, QuizSerializer,
    TaskGradeSerializer, ProjectGradeSerializer,  MoodleAPIConfigSerializer,
    AWSAccountSerializer,
    AwsMoodleUserSerializer,
    MoodleCourceProjectSerializer
)

User = get_user_model()

# CRUD Views for main entities (optional, you can expand these if needed)


class MoodleCourceProjectViewSet(viewsets.ModelViewSet):
    queryset = MoodleCourceProject.objects.all()
    serializer_class = MoodleCourceProjectSerializer


class ProjectListView(generics.ListCreateAPIView):
    queryset = Project.objects.all()
    serializer_class = ProjectSerializer


class ProjectDetailView(generics.RetrieveUpdateDestroyAPIView):
    queryset = Project.objects.all()
    serializer_class = ProjectSerializer


class TaskListView(generics.ListCreateAPIView):
    serializer_class = TaskSerializer

    def get_queryset(self):
        project_id = self.kwargs.get('project_id')
        return Task.objects.filter(project_id=project_id)


class TaskDetailView(generics.RetrieveUpdateDestroyAPIView):
    queryset = Task.objects.all()
    serializer_class = TaskSerializer


# Checker Views

class CheckerListView(generics.ListCreateAPIView):
    serializer_class = CheckerSerializer

    def get_queryset(self):
        task_id = self.kwargs.get('task_id')
        return Checker.objects.filter(task_id=task_id)


class CheckerDetailView(generics.RetrieveUpdateDestroyAPIView):
    queryset = Checker.objects.all()
    serializer_class = CheckerSerializer


# Resource Views

class ResourceListView(generics.ListCreateAPIView):
    serializer_class = ResourceSerializer

    def get_queryset(self):
        project_id = self.kwargs.get('project_id')
        return Resource.objects.filter(project_id=project_id)


class ResourceDetailView(generics.RetrieveUpdateDestroyAPIView):
    queryset = Resource.objects.all()
    serializer_class = ResourceSerializer


# Quiz Views

class QuizListView(generics.ListCreateAPIView):
    serializer_class = QuizSerializer

    def get_queryset(self):
        project_id = self.kwargs.get('project_id')
        return Quiz.objects.filter(project_id=project_id)


class QuizDetailView(generics.RetrieveUpdateDestroyAPIView):
    queryset = Quiz.objects.all()
    serializer_class = QuizSerializer


# Grade Views

class TaskGradeCreateUpdateView(generics.CreateAPIView):
    """
    Create or update a TaskGrade for the authenticated user.
    """
    serializer_class = TaskGradeSerializer

    def post(self, request, project_id, task_id):
        user = request.user
        task = get_object_or_404(Task, id=task_id, project_id=project_id)

        grade_value = request.data.get('grade')
        if grade_value is None:
            return Response({"detail": "Grade is required."}, status=status.HTTP_400_BAD_REQUEST)

        try:
            grade_value = float(grade_value)
        except ValueError:
            return Response({"detail": "Grade must be a number."}, status=status.HTTP_400_BAD_REQUEST)

        if not (0 <= grade_value <= 100):
            return Response({"detail": "Grade must be between 0 and 100."}, status=status.HTTP_400_BAD_REQUEST)

        task_grade, created = TaskGrade.objects.update_or_create(
            user=user,
            task=task,
            defaults={'grade': grade_value}
        )
        serializer = self.serializer_class(task_grade)
        return Response(serializer.data, status=status.HTTP_200_OK)


class ProjectGradeView(APIView):
    """
    Retrieve weighted project grade for the authenticated user.
    """

    def get(self, request, project_id):
        user = request.user
        project = get_object_or_404(Project, id=project_id)
        grade = project.weighted_grade_for_user(user)

        serializer = ProjectGradeSerializer({
            'user_id': user.id,
            'project_id': project.id,
            'grade': grade
        })

        return Response(serializer.data)


class MoodleAPIConfigViewSet(viewsets.ModelViewSet):
    queryset = MoodleAPIConfig.objects.all()
    serializer_class = MoodleAPIConfigSerializer


class AWSAccountViewSet(viewsets.ModelViewSet):
    queryset = AWSAccount.objects.all()
    serializer_class = AWSAccountSerializer


class AwsMoodleUserViewSet(viewsets.ModelViewSet):
    queryset = AwsMoodleUser.objects.all()
    serializer_class = AwsMoodleUserSerializer