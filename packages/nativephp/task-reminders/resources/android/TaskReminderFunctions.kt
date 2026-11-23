package com.bloomriver.taskreminders

import android.Manifest
import android.content.pm.PackageManager
import android.os.Build
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import androidx.fragment.app.FragmentActivity
import com.nativephp.mobile.bridge.BridgeError
import com.nativephp.mobile.bridge.BridgeFunction

object TaskReminderFunctions {
    private const val permissionRequestCode = 8172

    class RequestPermission(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            if (Build.VERSION.SDK_INT < 33) return mapOf("granted" to true, "requested" to false)
            val granted = ContextCompat.checkSelfPermission(activity, Manifest.permission.POST_NOTIFICATIONS) == PackageManager.PERMISSION_GRANTED
            if (!granted) {
                activity.runOnUiThread {
                    ActivityCompat.requestPermissions(activity, arrayOf(Manifest.permission.POST_NOTIFICATIONS), permissionRequestCode)
                }
            }
            return mapOf("granted" to granted, "requested" to !granted)
        }
    }

    class Schedule(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val taskId = (parameters["task_id"] as? Number)?.toInt() ?: throw BridgeError.InvalidParameters("task_id is required")
            val title = parameters["title"] as? String ?: throw BridgeError.InvalidParameters("title is required")
            val triggerAt = (parameters["trigger_at"] as? Number)?.toLong() ?: throw BridgeError.InvalidParameters("trigger_at is required")
            return mapOf("scheduled" to ReminderScheduler(activity).schedule(taskId, title, triggerAt))
        }
    }

    class Cancel(private val activity: FragmentActivity) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val taskId = (parameters["task_id"] as? Number)?.toInt() ?: throw BridgeError.InvalidParameters("task_id is required")
            ReminderScheduler(activity).cancel(taskId)
            return mapOf("cancelled" to true)
        }
    }
}
