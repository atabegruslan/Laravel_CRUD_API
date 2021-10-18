function markNotificationsAsRead(notificationCount)
{
	if (notificationCount !== '0')
	{
		$.get(baseUrl + 'markAsRead');
	}
}