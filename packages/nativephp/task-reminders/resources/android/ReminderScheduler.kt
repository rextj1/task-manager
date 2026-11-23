package com.bloomriver.taskreminders

import android.app.AlarmManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.os.Build
import android.util.Log
import org.json.JSONObject

internal class ReminderScheduler(private val context: Context) {
    private val alarms = context.getSystemService(Context.ALARM_SERVICE) as AlarmManager
    private val preferences = context.getSharedPreferences("task_reminders", Context.MODE_PRIVATE)

    fun schedule(taskId: Int, title: String, triggerAt: Long): Boolean {
        if (triggerAt <= System.currentTimeMillis()) {
            cancel(taskId)
            return false
        }
        val pending = PendingIntent.getBroadcast(context, taskId, reminderIntent(taskId, title), PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
        return try {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                alarms.setAndAllowWhileIdle(AlarmManager.RTC_WAKEUP, triggerAt, pending)
            } else {
                alarms.set(AlarmManager.RTC_WAKEUP, triggerAt, pending)
            }
            preferences.edit().putString(taskId.toString(), JSONObject().put("title", title).put("trigger_at", triggerAt).toString()).apply()
            true
        } catch (exception: Exception) {
            Log.e("TaskReminders", "Could not schedule reminder for task $taskId", exception)
            false
        }
    }

    fun cancel(taskId: Int) {
        val pending = PendingIntent.getBroadcast(context, taskId, reminderIntent(taskId, ""), PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
        alarms.cancel(pending)
        preferences.edit().remove(taskId.toString()).apply()
    }

    fun rescheduleAll() {
        preferences.all.forEach { (key, raw) ->
            val id = key.toIntOrNull() ?: return@forEach
            try {
                val reminder = JSONObject(raw as String)
                schedule(id, reminder.getString("title"), reminder.getLong("trigger_at"))
            } catch (exception: Exception) {
                Log.e("TaskReminders", "Could not restore reminder for task $id", exception)
                preferences.edit().remove(key).apply()
            }
        }
    }

    private fun reminderIntent(taskId: Int, title: String): Intent = Intent(context, ReminderReceiver::class.java)
        .putExtra("task_id", taskId)
        .putExtra("title", title)
}
