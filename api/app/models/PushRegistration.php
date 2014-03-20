<?php
namespace models;

class PushRegistration extends Collection
{
	protected $guarded = array();
	protected $primaryKey = '_id';
	protected $table = 'push_registrations';

	// Fields (based on Parse.com: https://parse.com/docs/push_guide#top/Android)
	// --------------------------------------------------------------------------
	// badge: The current value of the icon badge for iOS apps. Changing this value on the PFInstallation will update the badge value on the app icon. Changes should be saved to the server so that they will be used for future badge-increment push notifications.
	// channels: An array of the channels to which a device is currently subscribed.
	// timeZone: The current time zone where the target device is located. This value is synchronized every time an Installation object is saved from the device (readonly).
	// deviceType: The type of device, "ios", "android", "winrt", "winphone", or "dotnet"(readonly).
	// installationId: Unique Id for the device used by Parse (readonly).
	// deviceToken: The Apple generated token used for iOS devices (readonly).
	// channelUris: The Microsoft-generated push URIs for Windows devices (readonly).
	// appName: The display name of the client application to which this installation belongs (readonly).
	// appVersion: The version string of the client application to which this installation belongs (readonly).
	// parseVersion: The version of the Parse SDK which this installation uses (readonly).
	// appIdentifier: A unique identifier for this instal

	public function app() {
		return $this->belongsTo('models\App');
	}

}
