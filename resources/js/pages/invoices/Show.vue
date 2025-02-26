<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ArrowLeft, Edit, Printer, Download, Trash2, Mail } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import Toast from '@/components/ui/toast.vue';
import type { Toast as ToastType } from '@/types';

// Define invoice item interface
interface InvoiceItem {
    description: string;
    quantity: number;
    price: number;
}

// Define invoice interface
interface Invoice {
    id: string;
    client_name: string;
    client_email: string;
    client_address: string;
    invoice_date: string;
    due_date: string;
    status: string;
    items: InvoiceItem[];
    notes?: string;
    terms: string;
}

// Define props for the invoice data
const props = defineProps<{
    invoice: Invoice
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Invoicing',
        href: '/dashboard',
    },
    {
        title: `Invoice #${props.invoice.id}`,
        href: `/invoices/${props.invoice.id}`,
    },
];

// Calculate subtotal
const subtotal = computed(() => {
    return props.invoice.items.reduce((sum: number, item: InvoiceItem) => sum + (item.quantity * item.price), 0);
});

// Calculate tax (20% VAT)
const tax = computed(() => {
    return subtotal.value * 0.20;
});

// Calculate total
const total = computed(() => {
    return subtotal.value + tax.value;
});

// Format currency
const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
};

// Delete invoice confirmation
const showDeleteConfirm = ref(false);
const deleteInvoice = () => {
    router.delete(`/invoices/${props.invoice.id}`);
};

// Toast system
const toasts = ref<ToastType[]>([]);
const toast = ({ title, description, variant = 'default', duration = 5000 }: {
    title: string;
    description?: string;
    variant?: 'default' | 'destructive' | 'success' | 'info' | 'warning';
    duration?: number;
}) => {
    const id = Math.random().toString(36).substring(2, 9);
    const newToast: ToastType = { id, title, description, variant, duration };
    
    toasts.value.push(newToast);
    
    setTimeout(() => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }, duration);
    
    return id;
};

// Function to send invoice by email
const sendInvoiceByEmail = () => {
    if (!props.invoice.client_email) {
        toast({
            title: "Erreur",
            description: "Le client n'a pas d'adresse email. Veuillez d'abord ajouter une adresse email au client.",
            variant: "destructive",
        });
        return;
    }
    
    router.post(route('invoices.sendEmail', { id: props.invoice.id }), {}, {
        onSuccess: () => {
            toast({
                title: "Email envoyé",
                description: `La facture a été envoyée par email à ${props.invoice.client_email}`,
                variant: "success",
            });
        },
        onError: (errors) => {
            toast({
                title: "Erreur",
                description: errors.error || "Une erreur est survenue lors de l'envoi de l'email",
                variant: "destructive",
            });
        }
    });
};
</script>

<template>
    <Head :title="`Invoice #${invoice.id}`" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Toast notifications -->
        <Toast :toasts="toasts" />
        
        <div class="container mx-auto py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Invoice #{{ invoice.id }}</h1>
                <div class="flex space-x-2">
                    <Button variant="outline" @click="router.visit('/dashboard')">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Dashboard
                    </Button>
                    <Button variant="outline" @click="router.visit(`/invoices/${invoice.id}/edit`)">
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </Button>
                    <Button variant="outline">
                        <Printer class="mr-2 h-4 w-4" />
                        Print
                    </Button>
                    <Button variant="outline" @click="router.visit(route('invoices.pdf', { id: invoice.id }))">
                        <Download class="mr-2 h-4 w-4" />
                        Télécharger PDF
                    </Button>
                    <Button variant="outline" @click="sendInvoiceByEmail">
                        <Mail class="mr-2 h-4 w-4" />
                        Envoyer par email
                    </Button>
                    <Button variant="destructive" @click="showDeleteConfirm = true">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                </div>
            </div>
            
            <!-- Status Badge -->
            <div class="mb-6">
                <span 
                    class="px-3 py-1 rounded-full text-sm font-medium"
                    :class="{
                        'bg-green-100 text-green-800': invoice.status === 'Paid',
                        'bg-yellow-100 text-yellow-800': invoice.status === 'Pending',
                        'bg-red-100 text-red-800': invoice.status === 'Overdue'
                    }"
                >
                    {{ invoice.status }}
                </span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Client Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Client Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <div class="text-sm text-gray-500">Client Name</div>
                                <div class="font-medium">{{ invoice.client_name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Client Email</div>
                                <div class="font-medium">{{ invoice.client_email }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Client Address</div>
                                <div class="font-medium whitespace-pre-line">{{ invoice.client_address }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Invoice Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>Invoice Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <div class="text-sm text-gray-500">Invoice Number</div>
                                <div class="font-medium">{{ invoice.id }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Invoice Date</div>
                                <div class="font-medium">{{ invoice.invoice_date }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Due Date</div>
                                <div class="font-medium">{{ invoice.due_date }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
            
            <!-- Invoice Items -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Invoice Items</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4">Description</th>
                                    <th class="text-right py-3 px-4">Quantity</th>
                                    <th class="text-right py-3 px-4">Price</th>
                                    <th class="text-right py-3 px-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in invoice.items" :key="index" class="border-b">
                                    <td class="py-3 px-4">{{ item.description }}</td>
                                    <td class="text-right py-3 px-4">{{ item.quantity }}</td>
                                    <td class="text-right py-3 px-4">{{ formatCurrency(item.price) }}</td>
                                    <td class="text-right py-3 px-4">{{ formatCurrency(item.quantity * item.price) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                <CardFooter class="flex justify-end">
                    <div class="w-1/3 space-y-2">
                        <div class="flex justify-between">
                            <div class="text-sm text-gray-500">Subtotal</div>
                            <div class="font-medium">{{ formatCurrency(subtotal) }}</div>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-sm text-gray-500">Tax (20%)</div>
                            <div class="font-medium">{{ formatCurrency(tax) }}</div>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <div class="text-sm font-medium">Total</div>
                            <div class="font-bold text-lg">{{ formatCurrency(total) }}</div>
                        </div>
                    </div>
                </CardFooter>
            </Card>
            
            <!-- Additional Information -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Additional Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-if="invoice.notes">
                            <div class="text-sm text-gray-500">Notes</div>
                            <div class="font-medium whitespace-pre-line">{{ invoice.notes }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Terms and Conditions</div>
                            <div class="font-medium whitespace-pre-line">{{ invoice.terms }}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>
            
            <!-- Delete Confirmation Dialog -->
            <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                    <h3 class="text-lg font-bold mb-4">Confirm Deletion</h3>
                    <p class="mb-6">Are you sure you want to delete this invoice? This action cannot be undone.</p>
                    <div class="flex justify-end space-x-2">
                        <Button variant="outline" @click="showDeleteConfirm = false">Cancel</Button>
                        <Button variant="destructive" @click="deleteInvoice">Delete</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 