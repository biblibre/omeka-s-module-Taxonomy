const { root } = require("postcss");

function createItem(title, date, authors, hidden = false)
{
    cy.get('#menu .items').click();
    cy.get('#page-actions .button').click();

    cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').click();
    if (!Array.isArray(title))
    {
      cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').type(title);
    }

    if (Array.isArray(title) && title.length > 0)
    {
      cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').type(title[0]);
        for (let i = 1; i < title.length; i++)
        {
          cy.get('#properties [data-property-term="dcterms:title"] .button.add-value[data-type="literal"]').click();
          cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').eq(i).click();
          cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').eq(i).type(title[i])
        }
    }
    cy.get('#property-selector').contains('Dublin Core').click();
    if (date != '')
    {
      cy.get('#property-selector [data-child-search="Date"] .selectable').click();
    }
    if (authors.length > 0)
    {
      cy.get('#property-selector [data-child-search="Creator"] .selectable').click();
    }
    if (date != '')
    {
      cy.get('#properties [data-property-term="dcterms:date"] .button.add-value[data-type="literal"]').click();
      cy.get('#properties [data-property-term="dcterms:date"] .inputs textarea').click();
      cy.get('#properties [data-property-term="dcterms:date"] .inputs textarea').type(date);
    }
    if (authors.length > 0)
    {
        for (let i = 0; i < authors.length; i++)
        {
          cy.get('#properties [data-property-term="dcterms:creator"] .button.add-value[data-type="literal"]').click();
          cy.get('#properties [data-property-term="dcterms:creator"] .inputs textarea').eq(i).click();
          cy.get('#properties [data-property-term="dcterms:creator"] .inputs textarea').eq(i).type(authors[i])
        }
    }
    if (hidden)
    {
      cy.get('.button[title="Make private"]').click();
    }
    cy.get('#page-actions [name="add-item-submit"]').click();
}

function verifyItemsInOrder(itemNames, strict, orderMatters)
{
  for (let i = 0; i < itemNames.length; i++)
  {
    if (orderMatters)
    {
      cy.get('.items.resource').eq(i).contains(itemNames[i]).should('exist');
    }
    else
    {
      cy.get('.items.resource').contains(itemNames[i]).should('exist');
    }
  }
  if (strict)
  {
     cy.get('.items.resource').should('have.length', itemNames.length);
  }
}

function registerAndLogin()
{
    cy.get('[name="user[email]"]').click();
    cy.get('[name="user[email]"]').clear();
    cy.get('[name="user[email]"]').type('cypress@admin.com');
    cy.get('[name="user[email-confirm]"]').click();
    cy.get('[name="user[email-confirm]"]').clear();
    cy.get('[name="user[email-confirm]"]').type('cypress@admin.com');
    cy.get('[name="user[name]"]').click();
    cy.get('[name="user[name]"]').clear();
    cy.get('[name="user[name]"]').type('cypress');
    cy.get('[name="user[password-confirm][password]"]').click();
    cy.get('[name="user[password-confirm][password]"]').clear();
    cy.get('[name="user[password-confirm][password]"]').type('cypress');
    cy.get('[name="user[password-confirm][password-confirm]"]').click();
    cy.get('[name="user[password-confirm][password-confirm]"]').clear();
    cy.get('[name="user[password-confirm][password-confirm]"]').type('cypress');
    cy.get('[name="settings[installation_title]"]').click();
    cy.get('[name="settings[installation_title]"]').clear();
    cy.get('[name="settings[installation_title]"]').type('cypress');
    cy.get('#installationform [name="submit"]').click();
    login();
}

function login()
{
    cy.get('[name="email"]').click();
    cy.get('[name="email"]').clear();
    cy.get('[name="email"').type('cypress@admin.com');
    cy.get('[name="password"]').clear();
    cy.get('[name="password"]').type('cypress');
    cy.get('#loginform [name="submit"]').click();
}

describe('Solr', () =>
{
  it('passes', function() {
    cy.on('uncaught:exception', (err, runnable) => {
        // omekas/install has a weird exception that will fail the test
        // https://docs.cypress.io/api/cypress-api/catalog-of-events#Uncaught-Exceptions

        // using mocha's async done callback to finish
        // this test so we prove that an uncaught exception
        // was thrown

        // return false to prevent the error from
        // failing this test
        return false;
    });

    cy.visit('http://omekas');

    registerAndLogin();

    cy.get('#menu .modules').click();
    cy.get('#modules [action="/admin/module/install?id=Taxonomy"]').click();
    cy.get('#modules [action="/admin/module/deactivate?id=Taxonomy"]').should('exist');
    cy.get('#menu [href="/admin/resource-template"]').click();
    cy.get('#page-actions .button[href="/admin/resource-template/add"]');
    cy.get('input[name="o:label"]').click();
    cy.get('input[name="o:label"]').clear();
    cy.get('input[name="o:label"]').type('Taxonomy template');
    cy.get('.selector-parent[data-vocabulary-id="1"]').click();
    cy.get('.selector-parent[data-vocabulary-id="1"] [data-property-id="9"]').click();
    cy.get('#properties [data-property-id="9"] .actions .o-icon-edit').click();
    cy.get('#alternate-label').click();
    cy.get('#alternate-label').clear();
    cy.get('#alternate-label').type('Taxonomy');
    cy.get('#data_type_chosen').click();
    cy.contains('#data_type_chosen .chosen-results .active-result', 'Taxonomy').click();
    cy.get('#set-changes').click();
    cy.get('#page-actions button').click();
    cy.get('.messages .success').should('exist');

    cy.get('#menu [href="/admin/taxonomy"]').click();
    cy.get('#page-actions .button').click();
    cy.contains('.resource-property.field', 'dcterms:title').within(() => {
      cy.root().get('textarea').click(); 
      cy.root().get('textarea').clear();
      cy.root().get('textarea').type('My Taxonomy');
    })
    cy.get('#taxonomy-label').click();
    cy.get('input[name="o:code"]').click();
    cy.get('input[name="o:code"]').clear();
    cy.get('input[name="o:code"]').type('MYTAX');
    cy.get('#page-actions [type="submit"]').click();
    cy.get('.messages .success').should('exist');
    
    cy.get('#menu .items').click();
    cy.get('#page-actions .button').click();

    cy.get('#resource-template-select').closest('.inputs').within(() => {
      cy.root().click();
      cy.root().contains('.chosen-result', 'Taxonomy template').click();
    });

    cy.get('#properties [data-property-term="dcterms:form"] .inputs .o-icon-taxonomies').click();
    cy.contains('.taxonomies .resource-list a', 'My Taxonomy').click();
    cy.get('#select-item').click();

    cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').click();
    cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').clear();
    cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').type('Item with taxonomy');

    cy.get('#page-actions button[type="submit]').click();

    cy.get('#menu [href="/admin/taxonomy"]').click();
    cy.contains('.resource-name', 'My Taxonomy').click();
    cy.get('#page-actions a.button').eq(1).click();
    cy.get('#page-actions a.button').eq(1).click();

    cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').click();
    cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').clear();
    cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').type('Tag');

    cy.get('#taxonomy-term-label').click();
    cy.get('input[name="o:code"]').click();
    cy.get('input[name="o:code"]').clear();
    cy.get('input[name="o:code"]').type('MYTAG');

    cy.get('#page-actions input[type="submit"]').click();
    cy.get('.messages .success').should('exist');

    cy.get('#page-actions a.button').eq(1).click();

    cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').click();
    cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').clear();
    cy.get('#properties [data-property-term="dcterms:title"] .inputs textarea').type('Tag2');

    cy.get('#taxonomy-term-label').click();
    cy.get('input[name="o:code"]').click();
    cy.get('input[name="o:code"]').clear();
    cy.get('input[name="o:code"]').type('MYTAG2');

    cy.get('.taxonomy-term-form-select').click();
    cy.contains('.resource .taxonomy-term a', 'MYTAG').click();

    cy.get('#page-actions input[type="submit"]').click();
    cy.get('.messages .success').should('exist');
  });
});
