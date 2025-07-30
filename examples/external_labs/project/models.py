from django.db import models
from django.contrib.auth import get_user_model
from django.db.models import JSONField
User = get_user_model()


class MoodleAPIConfig(models.Model):
    api_url = models.URLField()
    token = models.CharField(max_length=255)

    def __str__(self):
        return f"Moodle API Config for {self.api_url}"


class AWSAccount(models.Model):
    account_id = models.CharField(max_length=255, unique=True)
    access_key = models.CharField(max_length=255)
    secret_key = models.CharField(max_length=255)

    def __str__(self):
        return f"AWS Account {self.account_id}"

class AwsMoodleUser(models.Model):
    moodle_api_config = models.ForeignKey(MoodleAPIConfig, on_delete=models.CASCADE, related_name='aws_moodle_users')
    aws_account = models.ForeignKey(AWSAccount, on_delete=models.CASCADE, related_name='moodle_users')
    moodle_user_id = models.CharField(max_length=255)

    class Meta:
        unique_together = ( 'aws_account','moodle_api_config')

    def __str__(self):
        return f"{self.user.username} - {self.aws_account.account_id}"




class Project(models.Model):
    title = models.CharField(max_length=255)
    description = models.TextField(blank=True)
    start_date = models.DateField(null=True, blank=True)
    end_date = models.DateField(null=True, blank=True)

    def __str__(self):
        return self.title

    def weighted_grade_for_user(self, user):
        task_grades = TaskGrade.objects.filter(user=user, task__project=self, grade__isnull=False)
        total_weight = sum(tg.task.weight for tg in task_grades)
        if total_weight == 0:
            return None
        weighted_sum = sum(tg.grade * tg.task.weight for tg in task_grades)
        return weighted_sum / total_weight


class Task(models.Model):
    project = models.ForeignKey(Project, on_delete=models.CASCADE, related_name='tasks')
    title = models.CharField(max_length=255)
    description = models.TextField(blank=True)
    order_index = models.PositiveIntegerField()
    weight = models.FloatField(default=1.0)  # Weight for grading

    def __str__(self):
        return f"{self.title} ({self.project.title})"


class Checker(models.Model):
    CHECKER_TYPES = (
        ('automated_code', 'Automated Code Checker'),
        ('manual_review', 'Manual Review'),
        ('plagiarism', 'Plagiarism Checker'),
        # Add more as needed
    )

    task = models.ForeignKey(Task, on_delete=models.CASCADE, related_name='checkers')
    checker_type = models.CharField(max_length=50, choices=CHECKER_TYPES)
    config = JSONField(blank=True, null=True)  # JSON config for checker

    def __str__(self):
        return f"{self.checker_type} checker for {self.task.title}"


class Resource(models.Model):
    RESOURCE_TYPES = (
        ('video', 'Video'),
        ('document', 'Document'),
        ('link', 'External Link'),
        # Add more as needed
    )

    project = models.ForeignKey(Project, on_delete=models.CASCADE, related_name='resources')
    resource_type = models.CharField(max_length=50, choices=RESOURCE_TYPES)
    url_or_path = models.URLField()
    title = models.CharField(max_length=255)

    def __str__(self):
        return f"{self.title} ({self.resource_type})"


class Quiz(models.Model):
    QUIZ_TYPES = (
        ('multiple_choice', 'Multiple Choice'),
        ('true_false', 'True/False'),
        ('coding', 'Coding Challenge'),
        ('essay', 'Essay'),
        # Add more as needed
    )

    project = models.ForeignKey(Project, on_delete=models.CASCADE, related_name='quizzes')
    quiz_type = models.CharField(max_length=50, choices=QUIZ_TYPES)
    title = models.CharField(max_length=255)
    settings = JSONField(blank=True, null=True)  # Quiz specific settings

    def __str__(self):
        return f"{self.title} ({self.quiz_type})"


class TaskGrade(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='task_grades')
    task = models.ForeignKey(Task, on_delete=models.CASCADE, related_name='grades')
    grade = models.FloatField()
    graded_at = models.DateTimeField(auto_now=True)

    class Meta:
        unique_together = ('user', 'task')

    def __str__(self):
        return f"{self.user} - {self.task}: {self.grade}"



class MoodleCourceProject(models.Model):
    moodle_course_id = models.CharField(max_length=255, unique=True)
    title = models.CharField(max_length=255)
    start_date = models.DateField(null=True, blank=True)
    end_date = models.DateField(null=True, blank=True)
    moodle_api_config = models.ForeignKey(MoodleAPIConfig, on_delete=models.CASCADE, related_name='moodle_course_projects')
    projects = models.ManyToManyField('Project', related_name='moodle_course_projects')

    def __str__(self):
        return self.title
