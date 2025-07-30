from django.contrib import admin

# Register your models here.
from .models import (
    MoodleAPIConfig,
    AWSAccount,
    AwsMoodleUser,
    Project,
    Task,
    Checker,
    Resource,
    Quiz,
    TaskGrade,
)
admin.site.register(MoodleAPIConfig)
admin.site.register(AWSAccount)
admin.site.register(AwsMoodleUser)
admin.site.register(Project)
admin.site.register(Task)
admin.site.register(Checker)
admin.site.register(Resource)
admin.site.register(Quiz)
admin.site.register(TaskGrade)