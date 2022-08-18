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

public class NewsData {

	String titlesection = 'Название раздела';
	String aliassection = 'Название раздела';
	String title = 'Название новости';
	String аtext = 'Анонсный текст новости';
	String ftext = 'Полный текст новости';
	String link = 'https://www.google.ru/';
	String checklink = 'https://www.google.ru/';
	String alias = 'psevdonim/';

	void setChangeData() {

		titlesection = 'Название раздела';
		aliassection = 'Название раздела';
		title = 'Редактированное название новости';
		аtext = 'Редактированный анонсный текст новости';
		ftext = 'Редактированный полный текст новости';
		link = 'https://www.google.ru/';
		checklink = 'https://www.google.ru/';
		alias = 'psevdonim';
	}
}
