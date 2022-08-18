import React from 'react';
import {Button, Col, Icon, Row, Spin, Tree} from 'antd';
import { connect } from 'dva';
import { groupBy } from 'lodash';
import classNames from 'classnames';
import router from "umi/router";
import * as Unicons from '@iconscout/react-unicons';
import Scrollbars from 'react-custom-scrollbars';
import EditSectionForm from '../EditSectionForm/Index';
import * as treeApi from '../../../dispatches/tree';
import style from './style.less';
import mainStyles from '../../../layouts/_sk/MainLayout.less';
import { vh, addEvent } from '../helpers/Index';
import * as sk from "../../../services/_sk/api";
import SkCanapeLoader from '../SkCanapeLoader/index'
import * as treeAPI from "@/dispatches/tree";
import {renderToString} from "react-dom/server";

const { TreeNode } = Tree;

@connect(({ skGlobal, loading }) => {
  return {
    sidebarActiveItem: skGlobal.sidebarActiveItem,
    activeTab: skGlobal.activeTab,
    loading:
      !!loading.effects['skGlobal/getSubItems'] ||
      !!loading.effects['skGlobal/deleteSection'] ||
      !!loading.effects['skGlobal/saveSection'],
  };
})
class Index extends React.Component {

  state = {maxHeight: vh()};

  componentDidMount() {

    const {leftPanelItemId} = this.props;

    if (leftPanelItemId){
      this.selectSection(leftPanelItemId);
    }

    addEvent(window, "resize", (event)  => {
      this.setState({
        maxHeight: vh()
      });
    });

  }

  componentDidUpdate(prevProps, prevState, snapshot) {

    const {
      leftPanelItemId: activeItemIdCur,
      sidebarActiveItem,
      moduleName
    } = this.props;

    const {
      leftPanelItemId: activeItemIdPrev,
      sidebarActiveItemPrev
    } = prevProps;

    // Дерево находится в активной вкладке родителя
    const locatedInsideActivePanel = moduleName === sidebarActiveItem;

    // Если параметры роутинга изменились и дерево находится в активной вкладке
    if ( ((activeItemIdCur !== activeItemIdPrev) || (sidebarActiveItemPrev !== sidebarActiveItem)) && locatedInsideActivePanel ){
      this.selectSection(activeItemIdCur);
    }

  }

  selectSection = (itemId) => {
    const {dispatch, moduleData} = this.props;

    const sections = moduleData.getIn(['params', 'items']).toJS();

    // ид добавленного раздела
    const addedSectionId = moduleData.get('addedSectionId', 0);

    // Если раздела, который должен быть выбран, нет в сторе
    if ( parseInt(itemId) ){

      if ( addedSectionId === parseInt(itemId) ){

        dispatch({
          type: 'skGlobal/clearAddedSectionId',
          payload: {
            path: moduleData.get('path')
          }
        });

        dispatch({
          type: 'skGlobal/getTree',
          payload: {
            path: moduleData.get('path'),
            sectionId: itemId
          }
        });

      } else if ( !Object.keys(sections).includes(itemId.toString()) ){

        dispatch({
          type: 'skGlobal/getTree',
          payload: {
            path: moduleData.get('path'),
            sectionId: itemId
          }
        });
      }
    }
  };

  onSelect = (selectedKeys, info) => {
    const { dispatch, moduleData, openNodes, sidebarActiveItem, activeTab, leftPanelItemId, moduleName, isMobile, onCollapse} = this.props;

    const { node } = info;

    if (selectedKeys.length) {
      router.push({
        pathname: `/out.left.${sidebarActiveItem}=${selectedKeys[0]};out.tabs=${activeTab}`
      });
    } else {
      // обновляем табы для выбранного раздела
      treeAPI.dispatchLoadTabs(dispatch, leftPanelItemId, moduleName)();
    }

    if (!node.isLeaf()) {
      if (!node.getNodeChildren().length) {
        // Загружаем поддерево
        treeApi.dispatchGetSubItems(dispatch, node.props.eventKey, moduleData.get('path'));
      }

      if (!openNodes.includes(node.props.eventKey)) {
        // Меняем state, добавляя данный узел в список "раскрытых"
        dispatch({
          type: 'skGlobal/extendedTree',
          payload: {
            expandedKeys: openNodes.concat([node.props.eventKey]),
            path: moduleData.get('path')
          }
        });
      }
    }

    // в мобильной версии после выбора пункта меню --> закрываем меню
    if (isMobile) {
      onCollapse(true);
    }
  };

  handleDeleteSection = (section) => ev => {
    // останавливаем всплытие события,
    // для того чтобы событие onClick не сработало на родитеских элементах
    ev.stopPropagation();

    const { dispatch, moduleData } = this.props;

    const headerModalWindow = (
      <span className="sk-modal-delete-header">
        {moduleData.getIn(['init', 'lang', 'treeDelRowHeader'])}
      </span>
    );
    const textModalWindow = (
      <div dangerouslySetInnerHTML={{
        __html: renderToString((
          <div className="sk-modal-delete">
            {/* Удалить "имя_раздела"? */}
            <div>{sk.dict('delRow').replace('{0}', section.title)}</div>
            <div>{`${moduleData.getIn(['init', 'lang', 'treeDelMsg'])}`}</div>
          </div>
        ))
      }} />
    );

    sk.showModal(headerModalWindow, textModalWindow, () => {
      dispatch({
        type: 'skGlobal/deleteSection',
        payload: {
          path: moduleData.get('path'),
          sectionId: section.id,
        },
      });
    });

  };

  hideWindow = () => {
    const {dispatch} = this.props;

    treeApi.dispatchToggleModalForm(dispatch, {
      isFetchReady: false,
      isShowModal: false,
    })
  };

  renderModalWindowEditTreeNode = () => {
    const {moduleData, openNodes} = this.props;
    const sections = moduleData.getIn(['params', 'items']).toJS();

    return <EditSectionForm
      parentModulePath={moduleData.get('path')}
      hideWindow={this.hideWindow}
      expandedKeys={openNodes}
      sections={sections}
    />;
  };

  handleEditTreeNode = item => () => {
    const { dispatch, moduleData } = this.props;

    treeApi.dispatchGetForm(dispatch, item, moduleData.get('path'));
    treeApi.dispatchToggleModalForm(dispatch, {isShowModal: true})
  };

  renderBranch = (items, parentPath = []) => {
    return Object.entries(items).map(([index, value]) => {
      const children =
        value.children && value.children.length
          ? this.renderBranch(value.children, parentPath.concat(index, 'children'))
          : null;
      const isLeaf = value.children !== undefined && !value.children.length;



      const title = (
        <span className={classNames("tree-item", {"tree-item--hidden" : ![1,2,-1].includes(value.visible) })}>
          <span title={value.title} className="tree-item__title">{value.title}</span>
          <span className="tree-item__elements">
            <span className="tree-item__id">{value.id}</span>
            <i
              className="unicons tree-item__edit"
              type="uil-edit"
              title={sk.dict("edit")}

              onClick={this.handleEditTreeNode(value)}
            >
              <Unicons.UilEditAlt size="14" />
            </i>

            <i
              className="unicons tree-item__delete"
              type="uil-trash"
              title={sk.dict("delete")}

              onClick={this.handleDeleteSection(value)}
            >
              <Unicons.UilTrash size="14" />
            </i>
          </span>
        </span>
      );

      return (
        <TreeNode
          icon={({skData}) => {

            // Тип "директория" ?
            if ( skData.type === 1 ){
              return <Icon type="folder" />
            }

            if ( !skData.type ){
              return skData.link
                ? <Unicons.UilLinkAlt className="unicons" size="14" />
                : <Unicons.UilFileBlank className="unicons" size="14" />
            }

          }}
          switcherIcon={(props) => {
            const {isLeaf, expanded, children} = props;

            // если был раскрыт
            if (expanded) {
              // но не имеет потомков
              if (!children) {
                return <span></span>;
              }
              return <Unicons.UilMinus className="unicons" size="14"/>
            }

            // если свернут
            if (!expanded) {
              // если тип узла не содержащий потомков
              if (isLeaf) {
                return <span></span>;
              }
              return <Unicons.UilPlus className="unicons" size="14"/>
            }

            return null;

          }}
          title={title}
          key={value.id}
          nodeId={value.id}
          skData={value}
          isLeaf={isLeaf}
          children={children}
        />
      );
    });
  };

  onExpand = (expandedKeys, { expanded, node }) => {
    const { dispatch, moduleData } = this.props;

    const { children, nodeId } = node.props;

    if (expanded && (!children || !children.length)) {
      // Запрос поддерева
      treeApi.dispatchGetSubItems(dispatch, nodeId, moduleData.get('path'));
    }

    // Обновляем стор
    dispatch({
      type: 'skGlobal/extendedTree',
      payload: {
        expandedKeys,
        path: moduleData.get('path')
      }
    });
  };

  // todo Перенести в сервисы
  collect = (sectionId, listAllSection) => {
    if (!listAllSection[sectionId]) return [];

    const out = [];

    listAllSection[sectionId].forEach(val => {
      if (!listAllSection[val.id]) {
        // val.children = [];
        out.push(val);
      } else {
        val.children = this.collect(val.id, listAllSection);
        out.push(val);
      }
    });

    out.sort((a, b) => {

      if ( a.position === b.position ){
        return 0;
      }

      return a.position > b.position ? 1 : -1
    });

    return out;
  };

  handleClickAddSection = () => {

    const { dispatch, moduleData, typeNewPage, leftPanelItemId } = this.props;

    const rootSectionId = moduleData.getIn(['init', 'rootSection']);

    const item = {
      id: 0,
      title: moduleData.getIn(['init','lang', 'treeNewSection']),
      parent: parseInt(leftPanelItemId || rootSectionId),
      type: typeNewPage,
      visible: 1,
    };

    treeApi.dispatchGetForm(dispatch, item, moduleData.get('path'));

    // Показать модальное окно
    treeApi.dispatchToggleModalForm(dispatch, {isShowModal: true})
  };

  renderPanelBlock = () => {

    const {addButtons, moduleData} = this.props;

    return (
      <Row gutter={8}>
        <Col span={12}>
          <Button
            type="primary"
            className="sk-main-button"
            htmlType="button"
            block
            size="small"
            onClick={this.handleClickAddSection}
          >
            {moduleData.getIn(['init','lang','add'])}
          </Button>
        </Col>
        {addButtons}
      </Row>
    )

  };

  onDragEnter = info => {
  };

  onDrop = info => {
    const {openNodes} = this.props;
    const {children, nodeId, skData} = info.node.props;
    const dropKey = info.node.props.eventKey;
    const dragKey = info.dragNode.props.eventKey;
    const dropPos = info.node.props.pos.split('-');
    const dropPosition = info.dropPosition - Number(dropPos[dropPos.length - 1]);

    const {dispatch, moduleData} = this.props;

    // при переносе раздела в тот же родитель
    if (
      dropPosition === 0
      && skData.children
      && skData.children.some((elem) => {return elem.id === parseInt(dragKey)})
    ) {
      return;
    }

    dispatch({
      type: 'skGlobal/changePosition',
      payload: {
        path: moduleData.get('path'),
        dropKey,
        dragKey,
        dropPos,
        dropPosition
      }
    });

    if (!children || !children.length) {
      treeApi.dispatchGetSubItems(dispatch, nodeId, moduleData.get('path'));
    }

    dispatch({
      type: 'skGlobal/extendedTree',
      payload: {
        expandedKeys: openNodes.concat([dropKey]),
        path: moduleData.get('path')
      }
    });
  };

  render() {
    const { moduleData, openNodes } = this.props;

    // todo
    if (!moduleData) {
      return '';
    }

    // console.log('Список items', moduleData.getIn(['params', 'items']).toJS());

    const groupByParent = groupBy(moduleData.getIn(['params', 'items']).toJS(), val => {
      return val.parent;
    });

    const baseNodeId = moduleData.getIn(['init', 'rootSection']);

    // console.log('Сгруппированные по parent', groupByParent);

    // записи в виде дерева
    const itemsInTreeView = this.collect(baseNodeId, groupByParent);

    // console.log('Дерево', itemsInTreeView);

    const treeNodes = this.renderBranch(itemsInTreeView);
    // console.log('state', this.state);
    const { maxHeight } = this.state;
    const { loading, leftPanelItemId, isMobile } = this.props;

    let leftItemsCnt = 5;
    const leftPanel = document.getElementsByClassName('sk-left-panel')[0];
    if (leftPanel) {
      leftItemsCnt = leftPanel.childElementCount;
    }

    const maxHeightScrollBar = isMobile ? maxHeight - (leftItemsCnt*44+64+2*10+32) : maxHeight - (leftItemsCnt*44+64+2*10+32+33);

    return (
      <React.Fragment>
        <Spin
          // tip="Загрузка..."
          // indicator={<Icon type="loading" style={{ fontSize: 32 }} spin />}
          // indicator={<SkCanapeLoader width={40} fadein main={false} loadText={false} />}
          spinning={loading}
        >
          {this.renderPanelBlock()}
          <Scrollbars
            // This will activate auto-height
            className="sktree-scrollbar"
            autoHide
            autoHideTimeout={1000}
            autoHideDuration={200}
            autoHeight
            autoHeightMin={100}
            autoHeightMax={maxHeightScrollBar}
          >
            <Tree
              className="hide-file-icon"
              showIcon
              blockNode
              showLine
              onSelect={this.onSelect}
              expandedKeys={openNodes}
              selectedKeys={[leftPanelItemId]}
              onExpand={this.onExpand}
              draggable
              onDragEnter={this.onDragEnter}
              onDrop={this.onDrop}
            >
              {treeNodes}

            </Tree>
          </Scrollbars>
        </Spin>
        {this.renderModalWindowEditTreeNode()}
      </React.Fragment>
    );
  }
}

export default Index;
