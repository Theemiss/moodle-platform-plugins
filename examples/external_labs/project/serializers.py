from rest_framework import serializers
from .models import Project, Task, Checker, Resource, Quiz, TaskGrade, MoodleAPIConfig, AWSAccount, AwsMoodleUser, MoodleCourceProject

class ProjectSerializer(serializers.ModelSerializer):
    class Meta:
        model = Project
        fields = ['id', 'title', 'description', 'start_date', 'end_date']


class TaskSerializer(serializers.ModelSerializer):
    class Meta:
        model = Task
        fields = ['id', 'project', 'title', 'description', 'order_index', 'weight']


class CheckerSerializer(serializers.ModelSerializer):
    class Meta:
        model = Checker
        fields = ['id', 'task', 'checker_type', 'config']


class ResourceSerializer(serializers.ModelSerializer):
    class Meta:
        model = Resource
        fields = ['id', 'project', 'resource_type', 'url_or_path', 'title']


class QuizSerializer(serializers.ModelSerializer):
    class Meta:
        model = Quiz
        fields = ['id', 'project', 'quiz_type', 'title', 'settings']


class TaskGradeSerializer(serializers.ModelSerializer):
    task_title = serializers.CharField(source='task.title', read_only=True)
    project_title = serializers.CharField(source='task.project.title', read_only=True)

    class Meta:
        model = TaskGrade
        fields = ['id', 'user', 'task', 'task_title', 'project_title', 'grade', 'graded_at']
        read_only_fields = ['id', 'graded_at', 'task_title', 'project_title']

    def validate_grade(self, value):
        if not (0 <= value <= 100):
            raise serializers.ValidationError("Grade must be between 0 and 100.")
        return value


class ProjectGradeSerializer(serializers.Serializer):
    user_id = serializers.IntegerField()
    project_id = serializers.IntegerField()
    grade = serializers.FloatField(allow_null=True, required=False)



class MoodleAPIConfigSerializer(serializers.ModelSerializer):
    class Meta:
        model = MoodleAPIConfig
        fields = '__all__'


class AWSAccountSerializer(serializers.ModelSerializer):
    class Meta:
        model = AWSAccount
        fields = '__all__'


class AwsMoodleUserSerializer(serializers.ModelSerializer):
    moodle_api_config = MoodleAPIConfigSerializer(read_only=True)
    aws_account = AWSAccountSerializer(read_only=True)

    class Meta:
        model = AwsMoodleUser
        fields = '__all__'



class MoodleCourceProjectSerializer(serializers.ModelSerializer):
    moodle_api_config = serializers.PrimaryKeyRelatedField(queryset=MoodleAPIConfig.objects.all())
    projects = serializers.PrimaryKeyRelatedField(many=True, queryset=Project.objects.all())

    class Meta:
        model = MoodleCourceProject
        fields = '__all__'