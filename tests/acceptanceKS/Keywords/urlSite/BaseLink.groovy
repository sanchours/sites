package urlSite

import internal.GlobalVariable
import urlSite.constant;

public class BaseLink {

	private static final String CONST_TEST_ACCEPT = '/test-accept';

	public static final String CONST_ADMIN = '/admin';

	public static String getUrlDef(){
		return constant.urlDef;
	}

	public static String getUrlDefAdm(){
		return (getUrlDef()+CONST_ADMIN);
	}

	public static String transitionUrlTest(){
		return (getUrlDef()+CONST_TEST_ACCEPT);
	}

	public static String createMobileConfig(){
		return (getUrlDef()+CONST_TEST_ACCEPT+'/mobile');
	}
}