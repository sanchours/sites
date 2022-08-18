import React, {useEffect, useState} from 'react';
import {Spin, ConfigProvider} from "antd";
import DocumentTitle from 'react-document-title';
import { connect } from 'dva';
import { ContainerQuery } from 'react-container-query';
import classNames from 'classnames';
import pathToRegexp from 'path-to-regexp';
import Media from 'react-media';
import withRouter from 'umi/withRouter';
import { formatMessage } from 'umi/locale';
import Context from '../MenuContext';
import { menu, title } from '../../defaultSettings';
import SkCanapeLoader from '../../components/_sk/SkCanapeLoader/index'
import MainModule from '../../components/_sk/MainModule/Index'
import styles from './MainLayout.less';
import moment from 'moment';
import 'moment/locale/ru';
import 'moment/locale/de';
import enUS from 'antd/es/locale/en_US';
import ruRU from 'antd/es/locale/ru_RU';
import deDE from 'antd/es/locale/de_DE';

const query = {
  'screen-xs': {
    maxWidth: 575,
  },
  'screen-sm': {
    minWidth: 576,
    maxWidth: 767,
  },
  'screen-md': {
    minWidth: 768,
    maxWidth: 991,
  },
  'screen-lg': {
    minWidth: 992,
    maxWidth: 1199,
  },
  'screen-xl': {
    minWidth: 1200,
    maxWidth: 1599,
  },
  'screen-xxl': {
    minWidth: 1600,
  },
};

const BasicLayout = (props) => {

  const { location, breadcrumbNameMap, location: { pathname }, dropCacheEffect, headerLayoutData } = props;
  const [locale, setLocale] = useState(ruRU);
  let headerLang = 'ru';

  if (headerLayoutData.get('out.header.lang')) {
    headerLang = headerLayoutData.get('out.header.lang').getIn(['init', 'currentLang']);
  }

  // устанавливаем локаль для компонентов Ant
  useEffect(() => {
    switch (headerLang) {
      case 'en':
        setLocale(enUS);
        moment.locale('en');
        break;
      case 'de':
        setLocale(deDE);
        moment.locale('de');
        break;
      default:
        setLocale(ruRU);
        moment.locale('ru');
    }
  });

  const getContext = () => {
    return {
      location,
      breadcrumbNameMap,
    };
  };

  const matchParamsPath = (pathname, breadcrumbNameMap) => {
    const pathKey = Object.keys(breadcrumbNameMap).find(key => pathToRegexp(key).test(pathname));
    return breadcrumbNameMap[pathKey];
  };


  const getPageTitle = (pathname, breadcrumbNameMap) => {
    const currRouterData = matchParamsPath(pathname, breadcrumbNameMap);

    if (!currRouterData) {
      return title;
    }
    const pageName = menu.disableLocal
      ? currRouterData.name
      : formatMessage({
        id: currRouterData.locale || currRouterData.name,
        defaultMessage: currRouterData.name,
      });

    return `${pageName} - ${title}`;
  };

  return (
    <DocumentTitle title={getPageTitle(pathname, breadcrumbNameMap)}>
      <Spin spinning={dropCacheEffect} delay={0}>
        <ContainerQuery query={query}>
          {params => (
            <ConfigProvider locale={locale}>
              <Context.Provider value={getContext()}>
                <div key={locale ? locale.locale : 'en'} className={classNames(params)}>
                  <MainModule {...props} />
                </div>
              </Context.Provider>
            </ConfigProvider>
          )}
        </ContainerQuery>
      </Spin>
    </DocumentTitle>
  );

};

export default withRouter(connect(({ skGlobal, setting, menu: menuModel, loading }) => {
  return {
    storeInitialized: skGlobal.storeInitialized,
    breadcrumbNameMap: menuModel.breadcrumbNameMap,
    dropCacheEffect: !!loading.effects['skGlobal/dropCache'],
    loading: loading.effects['skGlobal/fetchInitData'],
    headerLayoutData: skGlobal.headerLayout,
    ...setting,
  };
})((props) => {

  const {dispatch, loading, storeInitialized} = props;

  useEffect(() => {
    if ( !storeInitialized ){
      dispatch({
        type: 'skGlobal/fetchInitData',
        payload: {},
      });
    }
  }, []);

  return (
    !loading && storeInitialized? (
      <Media query="(max-width: 599px)">
        {isMobile => <BasicLayout {...props} isMobile={isMobile} />}
      </Media>
    ) : (
      <SkCanapeLoader />
    )
  );
}));
