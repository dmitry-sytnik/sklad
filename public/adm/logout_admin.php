<?php require_once("../../includes/session.php") ?>
<?php require_once("../../includes/functions.php") ?><?php
	// ������ 1: ������� �����
	// ������ ���� �������� ������: session_start();
	
	
	if(isset($_SESSION["admin_id"])) {
		$_SESSION["admin_id"] = null;
		$_SESSION["username"] = null;
		redirect_to("../index.php");
	} else { //���� ��� �������� ������� ���-�������� ��������� �� ����� (������������, �� ������� $_SESSION["admin_id"]), �� ��������
		redirect_to("../index.php");} 
?><?php
/*
	// ������ 2: �������� ������
	// �������� ������ ��������� ������ � �������� ����� �� ��
	
	session_start();  //������ ���� �������� ������
	$_SESSION = array(); // ������ ������������ � ������ ������, �� ���� ��������� ��� �������� �� ��
	if (isset($_COOKIE[session_name()])){	//���� �� ����� ���� � ������ ������
		setcookie(session_name(), '', time()-42000, '/'); // �� ������ ����: � ���� ������, ��� ��������, 
														// � ����������� �� �������� ����� � �������
														// � ����� �������� �� ��������� ������ ��������
		}
	session_destroy(); // ������� ������ �� �������
	redirect_to("login.php");
*/
?>