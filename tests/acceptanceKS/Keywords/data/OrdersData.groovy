package data

import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject

import java.lang.reflect.Array

import com.kms.katalon.core.annotation.Keyword
import com.kms.katalon.core.checkpoint.Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords
import com.kms.katalon.core.model.FailureHandling
import com.kms.katalon.core.testcase.TestCase
import com.kms.katalon.core.testcase.TestCaseFactory
import com.kms.katalon.core.testdata.TestData
import com.kms.katalon.core.testdata.TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository
import com.kms.katalon.core.testobject.TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords

import internal.GlobalVariable

import MobileBuiltInKeywords as Mobile
import WSBuiltInKeywords as WS
import WebUiBuiltInKeywords as WebUI

public class OrdersData {

	String name = 'Имя пользователя';
	String pass = '123123';
	String email = 'test@test.test';
	String tel = '1234567890';
	String address = 'г.Тест ул.Тест д.Тест';
	String postcode = '4812409421';
	String text = 'Дополнительные пожелания';
	String telcont = '+7 (123) 456-7890';
	String status = 'status-new';
	String titlestatus = 'новый статус';
	String lettettitle = 'название письма';
	String lettertext = 'текст в письме';
	String passchange = '321321';
	String namesec = 'Другой клиент';
	String passsec = '123123';
	String telsec = '0987654321';
	String emailsec = 'qwer@qwer.qwer';

	void setChangeData() {

		name = 'Имя пользователя редактированое';
		pass = '123123';
		email = 'testsecond@testrest.test';
		tel = '0987654321';
		address = 'г.Тест ул.Тест д.Тест редактированое';
		postcode = '111111111';
		text = 'Дополнительные пожелания редактированое';
		telcont = '0987654321';
	}
}
