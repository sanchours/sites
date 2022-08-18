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

public class CatalogData {

	String titlesection = 'Название раздела';
	String aliassection = 'test-razdel';
	String itemname = 'Nazvanie tovara';
	String tecnicalname = 'technical-name';
	String time = '00:00';
	String price = '100';
	String factonalnumbers = '3.14';
	String string = 'строка';
	String textarea = 'Текст';
	String itemnamesec = 'Второй товар';
	String tecnicalnamesec = 'technical-name-2';
	String pricesec = '200';
	String count = '10';
	String pricex2 = '200';
	String pricex10 = '1 000';
	String priceicon = '400';
	String emptybasket = 'Корзина пуста'

	void setChangeData() {

		itemname = 'Название товар (изменённое)';
		tecnicalname = 'technical-name-change';
		time = '01:00';
		price = '200';
		factonalnumbers = '2.12';
		string = 'строка изменённая';
		textarea = 'Текст изменённый';
	}
}
